#!/usr/bin/env php
<?php

// This script should be added to root's nightly cron to read the
// current list of Bro packages using the bro-pkg command line utility.

// REQUIRED: Set the location of the bro-pkg-web .env file
// NOTE: This script should be run by user with read access to the .env file.
$envfile = '/var/www/bropkg/config/.env';

// Set the location of the bro-pkg command line exec
$broexec = '/usr/bin/bro-pkg';

// Read .env file and scan for important secrets
if (($envlines = file($envfile)) === false) {
    exit("Error. Unable to read $envfile .\n");
}
$githubtoken = searchEnvFile('GITHUBTOKEN', $envlines);
$sqldb       = searchEnvFile('DB_DATABASE', $envlines);
$sqlhost     = searchEnvFile('DB_HOST',     $envlines);
$sqluser     = searchEnvFile('DB_USERNAME', $envlines);
$sqlpass     = searchEnvFile('DB_PASSWORD', $envlines);

// Set up database connection
$sqldsn  = "mysql:host=$sqlhost;dbname=$sqldb;charset=utf8mb4";
$sqlopt  = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($sqldsn, $sqluser, $sqlpass, $sqlopt);
} catch (PDOException $e) {
    exit('Error. Database Connection Failed: ' . $e->getMessage() . "\n");
}

// Check if updater is already running
$running = false;
$stmt = $pdo->query('SELECT status FROM updater');
if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch();
    if ($row['status'] == 'running') {
        $running = true;
    }
} else {
    // First time running
    $stmt = $pdo->prepare("INSERT INTO updater (id,status) VALUES(1,'idle')");
    $stmt->execute();
}

if ($running) {
    exit("Error. The updater is already running.\n");
} else {
    // Write 'running' for updater status
    $stmt = $pdo->prepare("UPDATE updater SET status='running', " .
        "started=now() WHERE id=1;");
    $stmt->execute();
}

// Get a list of all bro packages
$pkglist = shell_exec("$broexec list all --nodesc");
$pkgarray = explode("\n", trim($pkglist));

foreach ($pkgarray as $pkg) {
    // Remove trailing 'local package' information
    $pkg = preg_replace('/\s.*$/', '', $pkg);

    echo "Processing package '$pkg'\n";

    // Write the currently processed package to the database
    $stmt = $pdo->prepare("UPDATE updater SET package=:pkg WHERE id=1;");
    $stmt->execute(['pkg' => $pkg]);

    // Get package info for the current package
    $pkginfo = shell_exec("$broexec info $pkg --json --nolocal --allvers");
    $pkgjson = json_decode($pkginfo, false);

    // Verify package name from JSON is correct
    $pkgname = key($pkgjson);
    if (is_null($pkgname)) {
        echo "Error. No package name in JSON for '$pkg'. Skipping.\n";
        continue;
    }
    if ($pkg != $pkgname) {
        echo "Error. Mismatched package name in JSON for '$pkg'. Skipping.\n";
        continue;
    }

    // Get the package URL
    if (property_exists($pkgjson->$pkg, 'url')) {
        $pkgurl = $pkgjson->$pkg->url;
    } else { 
        echo "Error. No URL IN JSON for '$pkg'. Skipping.\n";
        continue;
    }

    // Use $pkgurl to fetch the GitHub README file for the package.
    // https://developer.github.com/v3/repos/contents/#get-the-readme
    $apiurl = preg_replace('|github.com/|', 'api.github.com/repos/', $pkgurl);
    $apiurl .= '/readme';
    $readme = false;
    $ch = curl_init();
    if ($ch !== false) {
        curl_setopt($ch, CURLOPT_URL, $apiurl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl');
        curl_setopt($ch, CURLOPT_HTTPHEADER, 
            array("Authorization: token $githubtoken"));
        $output = curl_exec($ch);
        if (!empty($output)) {
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpcode == 200) {
                $readmejson = json_decode($output, false);
                if (property_exists($readmejson, 'content')) {
                    $readme = base64_decode($readmejson->content);
                }
            }
        }
        curl_close($ch);
    }

    // Get the database ID for the package (if any)
    $pkgid = '';
    $stmt = $pdo->prepare("SELECT id FROM packages WHERE name=:pkg;");
    $stmt->execute(['pkg' => $pkg]);
    if ($stmt->rowCount() > 0) { // Already in the database, so update it.
        $row = $stmt->fetch();
        $pkgid = $row['id'];
        $stmt = $pdo->prepare("UPDATE packages SET url=:pkgurl, " .
            "readme=:readme, modified=now() WHERE name=:pkg;");
        $stmt->execute([
            'pkg'    => $pkg, 
            'pkgurl' => $pkgurl, 
            'readme' => $readme
        ]);
    } else { // Package doesn't exist in the database. Insert it and get ID.
        $stmt = $pdo->prepare("INSERT INTO packages " .
            "VALUES(uuid(), :pkg, :pkgurl, :readme, now(), now());");
        $stmt->execute([
            'pkg'    => $pkg, 
            'pkgurl' => $pkgurl, 
            'readme' => $readme
        ]);
        $stmt = $pdo->prepare("SELECT id FROM packages WHERE name=:pkg;");
        $stmt->execute(['pkg' => $pkg]);
        if ($stmt->rowCount() > 0) { 
            $row = $stmt->fetch();
            $pkgid = $row['id'];
        } else {
            $pkgid = '';
        }
    }
    if (empty($pkgid)) {
        exit("Error. Could not get ID for '$pkg'.\n");
    }

    // Get all versions of metadatas for the package
    if (property_exists($pkgjson->$pkg, 'metadata')) {
        $versions = array_keys(get_object_vars($pkgjson->$pkg->metadata));
        foreach ($versions as $version) {
            echo "    Processing metadata version '$version'.\n";

            $description = null;
            $script_dir = null;
            $plugin_dir = null;
            $build_command = null;
            $user_vars = null;
            $test_command = null;
            $config_files = null;
            $depends = null;
            $external_depends = null;
            $suggests = null;
            $tags = null;
            if (property_exists($pkgjson->$pkg->metadata, $version)) {
                $description = (property_exists(
                    $pkgjson->$pkg->metadata->$version, 'description'
                ) ? $pkgjson->$pkg->metadata->$version->description : null);
                $script_dir = (property_exists(
                    $pkgjson->$pkg->metadata->$version, 'script_dir'
                ) ? $pkgjson->$pkg->metadata->$version->script_dir : null);
                $plugin_dir = (property_exists(
                    $pkgjson->$pkg->metadata->$version, 'plugin_dir'
                ) ? $pkgjson->$pkg->metadata->$version->plugin_dir : null);
                $build_command = (property_exists(
                    $pkgjson->$pkg->metadata->$version, 'build_command'
                ) ? $pkgjson->$pkg->metadata->$version->build_command : null);
                $user_vars = (property_exists(
                    $pkgjson->$pkg->metadata->$version, 'user_vars'
                ) ? $pkgjson->$pkg->metadata->$version->user_vars : null);
                $test_command = (property_exists(
                    $pkgjson->$pkg->metadata->$version, 'test_command'
                ) ? $pkgjson->$pkg->metadata->$version->test_command : null);
                $config_files = (property_exists(
                    $pkgjson->$pkg->metadata->$version, 'config_files'
                ) ? $pkgjson->$pkg->metadata->$version->config_files : null);
                $depends = (property_exists(
                    $pkgjson->$pkg->metadata->$version, 'depends'
                ) ? $pkgjson->$pkg->metadata->$version->depends : null);
                $external_depends = (property_exists(
                    $pkgjson->$pkg->metadata->$version, 'external_depends'
                ) ? $pkgjson->$pkg->metadata->$version->external_depends : null);
                $suggests = (property_exists(
                    $pkgjson->$pkg->metadata->$version, 'suggests'
                ) ? $pkgjson->$pkg->metadata->$version->suggests : null);
                $tags = (property_exists(
                    $pkgjson->$pkg->metadata->$version, 'tags'
                ) ? $pkgjson->$pkg->metadata->$version->tags : null);
            }

            // user_vars, depends, external_depends, and suggests need 
            // massaging to possibly convert from object to string 
            // (if not null)
            $user_vars = objToStr($user_vars);
            $depends = objToStr($depends);
            $external_depends = objToStr($external_depends);
            $suggests = objToStr($suggests);

            // Get the database ID for the metadata version (if any)
            $metaid = '';
            $stmt = $pdo->prepare("SELECT id FROM metadatas WHERE package_id=:pkgid and version=:version;");
            $stmt->execute(['pkgid' => $pkgid, 'version' => $version]);
            if ($stmt->rowCount() > 0) { // Already in the database, so update it.
                $row = $stmt->fetch();
                $metaid = $row['id'];
                $stmt = $pdo->prepare("UPDATE metadatas SET " .
                    "description=:description, " .
                    "script_dir=:script_dir, " .
                    "plugin_dir=:plugin_dir, " .
                    "build_command=:build_command, " .
                    "user_vars=:user_vars, " .
                    "test_command=:test_command, " .
                    "config_files=:config_files, " .
                    "depends=:depends, " .
                    "external_depends=:external_depends, " .
                    "suggests=:suggests, " .
                    "modified=now() " .
                    "WHERE id=:metaid;"
                );
                $stmt->execute([
                    'description' => $description,
                    'script_dir' => $script_dir,
                    'plugin_dir' => $plugin_dir,
                    'build_command' => $build_command,
                    'user_vars' => $user_vars,
                    'test_command' => $test_command,
                    'config_files' => $config_files,
                    'depends' => $depends,
                    'external_depends' => $external_depends,
                    'suggests' => $suggests,
                    'metaid' => $metaid
                ]);
            } else { // Package doesn't exist in the database. Insert it and get ID.

                $stmt = $pdo->prepare("INSERT INTO metadatas " .
                    "VALUES(uuid(), :pkgid, :version, " .
                    ":description, :script_dir, :plugin_dir, :build_command, " .
                    ":user_vars, :test_command, :config_files, " .
                    ":depends, :external_depends, :suggests, now(), now());");
                $stmt->execute([
                    'pkgid' => $pkgid,
                    'version' => $version,
                    'description' => $description,
                    'script_dir' => $script_dir,
                    'plugin_dir' => $plugin_dir,
                    'build_command' => $build_command,
                    'user_vars' => $user_vars,
                    'test_command' => $test_command,
                    'config_files' => $config_files,
                    'depends' => $depends,
                    'external_depends' => $external_depends,
                    'suggests' => $suggests
                ]);
                $stmt = $pdo->prepare("SELECT id FROM metadatas " .
                    "WHERE package_id=:pkgid AND version=:version;");
                $stmt->execute(['pkgid' => $pkgid, 'version' => $version]);
                if ($stmt->rowCount() > 0) { 
                    $row = $stmt->fetch();
                    $metaid = $row['id'];
                } else {
                    $metaid = '';
                }
            }
            if (empty($metaid)) {
                exit("Error. Could not get ID for version '$version'.\n");
            }

            // Process any tags for the metadata version
            if (!is_null($tags) && (strlen($tags) > 0)) {
                // Split tags on commas
                $tagsarray = array_map('trim', explode(',', $tags));
                foreach ($tagsarray as $tag) {
                    // Get the database ID for the tag (if any)
                    $tagid = '';
                    $stmt = $pdo->prepare("SELECT id FROM tags WHERE name=:tag;");
                    $stmt->execute(['tag' => $tag]);
                    if ($stmt->rowCount() > 0) { 
                        $row = $stmt->fetch();
                        $tagid = $row['id'];
                    } else { // Tag doesn't exist. Insert it and get ID.
                        $stmt = $pdo->prepare("INSERT INTO tags " .
                            "VALUES(uuid(), :tag, now(), now());");
                        $stmt->execute(['tag' => $tag]);
                        $stmt = $pdo->prepare("SELECT id FROM tags " .
                            "WHERE name=:tag;");
                        $stmt->execute(['tag' => $tag]);
                        if ($stmt->rowCount() > 0) { 
                            $row = $stmt->fetch();
                            $tagid = $row['id'];
                        } else {
                            $tagid = '';
                        }
                    }
                    if (empty($tagid)) {
                        exit("Error. Could not get ID for tag '$tag'.\n");
                    }

                    echo "        Processing tag '$tag'\n";

                    // Link the tag to the metadata version
                    $stmt = $pdo->prepare("INSERT IGNORE INTO metadatas_tags " .
                        "VALUES(:metaid, :tagid);");
                    $stmt->execute(['metaid' => $metaid, 'tagid' => $tagid]);
                }
            }

        }
    }
}

// Write 'idle' for updater status
$stmt = $pdo->prepare("UPDATE updater SET status='idle', ended=now(), package=NULL WHERE id=1;");
$stmt->execute();

function objToStr($obj) {
    $retval = null;
    if (!is_null($obj)) {
        $keys = array_keys(get_object_vars($obj));
        $str = '';
        foreach ($keys as $key) {
            $str .= $key . ' ' . $obj->$key . "\n";
        }
        $retval = trim($str);
    }
    return $retval;
}

function searchEnvFile($envvalue, $envlines) {
    $retval = '';
    $linematch = @array_values(preg_grep(
        '/export ' . $envvalue . '\s*=\s*".*"/', $envlines))[0];
    if (preg_match(
        '/export ' . $envvalue . '\s*=\s*"(.*)"/', $linematch, $matches)) {
        $retval = $matches[1];
    }
    if (strlen($retval) == 0) {
        exit("Error. Unable to read $envvalue from .env file.\n");
    }

    return $retval;
}

?>
