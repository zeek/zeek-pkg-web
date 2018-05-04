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
$sqlhost     = searchEnvFile('DB_HOST', $envlines);
$sqluser     = searchEnvFile('DB_USERNAME', $envlines);
$sqlpass     = searchEnvFile('DB_PASSWORD', $envlines);

// Read all info from bro-pkg into local variable $pkgs to
// make database updating as fast as possible.
$pkgs = array();

// Refresh the local bro package listing.
$refresh = shell_exec("$broexec refresh");

// Get a list of all bro packages
$pkglist = shell_exec("$broexec list all --nodesc");
$pkgarray = explode("\n", trim($pkglist));

$pkgcount = count($pkgarray);
if ($pkgcount > 0) {
    echo "Processing $pkgcount packages";
}
foreach ($pkgarray as $pkg) {
    echo ".";

    // Remove trailing 'local package' information
    $pkg = preg_replace('/\s.*$/', '', $pkg);


    // Get package info for the current package
    $pkginfo = shell_exec("$broexec info $pkg --json --nolocal --allvers");
    $pkgjson = json_decode($pkginfo, false);

    // Verify package name from JSON is correct
    $pkgname = key($pkgjson);
    if (is_null($pkgname)) {
        echo "\nError. No package name in JSON for '$pkg'. Skipping.\n";
        continue;
    }
    if ($pkg != $pkgname) {
        echo "\nError. Mismatched package name in JSON for '$pkg'. Skipping.\n";
        continue;
    }

    // Get the package URL
    if (property_exists($pkgjson->$pkg, 'url')) {
        $pkgurl = $pkgjson->$pkg->url;
        $pkgs[$pkg]['url'] = $pkgurl;
    } else {
        echo "\nError. No URL IN JSON for '$pkg'. Skipping.\n";
        continue;
    }

    // Use $pkgurl to fetch the GitHub README file for the package.
    // https://developer.github.com/v3/repos/contents/#get-the-readme
    $apiurl = preg_replace('|github.com/|', 'api.github.com/repos/', $pkgurl);
    $apiurl .= '/readme';
    $pkgs[$pkg]['readme'] = false;
    $pkgs[$pkg]['readme_name'] = null;
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
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: token $githubtoken"));
        $output = curl_exec($ch);
        if (!empty($output)) {
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpcode == 200) {
                $json = json_decode($output, false);
                if (property_exists($json, 'content')) {
                    $pkgs[$pkg]['readme'] = base64_decode($json->content);
                }
                if (property_exists($json, 'name')) {
                    $pkgs[$pkg]['readme_name'] = $json->name;
                }
            }
        }
        curl_close($ch);
    }

    // Use $pkgurl to fetch the GitHub stats for the package.
    // https://developer.github.com/v3/repos/#get
    $apiurl = preg_replace('|github.com/|', 'api.github.com/repos/', $pkgurl);
    $pkgs[$pkg]['subscribers_count'] = 0;
    $pkgs[$pkg]['stargazers_count'] = 0;
    $pkgs[$pkg]['open_issues_count'] = 0;
    $pkgs[$pkg]['forks_count'] = 0;
    $pkgs[$pkg]['pushed_at'] = date("Y-m-d H:i:s", strtotime('now'));
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
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: token $githubtoken"));
        $output = curl_exec($ch);
        if (!empty($output)) {
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpcode == 200) {
                $json = json_decode($output, false);
                if (property_exists($json, 'subscribers_count')) {
                    $pkgs[$pkg]['subscribers_count'] = $json->subscribers_count;
                }
                if (property_exists($json, 'stargazers_count')) {
                    $pkgs[$pkg]['stargazers_count'] = $json->stargazers_count;
                }
                if (property_exists($json, 'open_issues_count')) {
                    $pkgs[$pkg]['open_issues_count'] = $json->open_issues_count;
                }
                if (property_exists($json, 'forks_count')) {
                    $pkgs[$pkg]['forks_count'] = $json->forks_count;
                }
                if (property_exists($json, 'pushed_at')) {
                    $pkgs[$pkg]['pushed_at'] = date("Y-m-d H:i:s", strtotime($json->pushed_at));
                }
            }
        }
        curl_close($ch);
    }

    // Get all versions of metadatas for the package
    if (property_exists($pkgjson->$pkg, 'metadata')) {
        $versions = array_keys(get_object_vars($pkgjson->$pkg->metadata));
        foreach ($versions as $version) {
            if (property_exists($pkgjson->$pkg->metadata, $version)) {
                $pkgs[$pkg]['metadata'][$version]['description'] =
                    (property_exists($pkgjson->$pkg->metadata->$version, 'description')
                      ? $pkgjson->$pkg->metadata->$version->description
                      : null);
                $pkgs[$pkg]['metadata'][$version]['script_dir'] =
                    (property_exists($pkgjson->$pkg->metadata->$version, 'script_dir')
                      ? $pkgjson->$pkg->metadata->$version->script_dir
                      : null);
                $pkgs[$pkg]['metadata'][$version]['plugin_dir'] =
                    (property_exists($pkgjson->$pkg->metadata->$version, 'plugin_dir')
                      ? $pkgjson->$pkg->metadata->$version->plugin_dir
                      : null);
                $pkgs[$pkg]['metadata'][$version]['build_command'] =
                    (property_exists($pkgjson->$pkg->metadata->$version, 'build_command')
                      ? $pkgjson->$pkg->metadata->$version->build_command
                      : null);
                $pkgs[$pkg]['metadata'][$version]['user_vars'] =
                    (property_exists($pkgjson->$pkg->metadata->$version, 'user_vars')
                      ?  objToStr($pkgjson->$pkg->metadata->$version->user_vars)
                      : null);
                $pkgs[$pkg]['metadata'][$version]['test_command'] =
                    (property_exists($pkgjson->$pkg->metadata->$version, 'test_command')
                      ? $pkgjson->$pkg->metadata->$version->test_command
                      : null);
                $pkgs[$pkg]['metadata'][$version]['config_files'] =
                    (property_exists($pkgjson->$pkg->metadata->$version, 'config_files')
                      ? $pkgjson->$pkg->metadata->$version->config_files
                      : null);
                $pkgs[$pkg]['metadata'][$version]['depends'] =
                    (property_exists($pkgjson->$pkg->metadata->$version, 'depends')
                      ? objToStr($pkgjson->$pkg->metadata->$version->depends)
                      : null);
                $pkgs[$pkg]['metadata'][$version]['external_depends'] =
                    (property_exists($pkgjson->$pkg->metadata->$version, 'external_depends')
                      ? objToStr($pkgjson->$pkg->metadata->$version->external_depends)
                      : null);
                $pkgs[$pkg]['metadata'][$version]['suggests'] =
                    (property_exists($pkgjson->$pkg->metadata->$version, 'suggests')
                      ? objToStr($pkgjson->$pkg->metadata->$version->suggests)
                      : null);
                $pkgs[$pkg]['metadata'][$version]['tags'] =
                    (property_exists($pkgjson->$pkg->metadata->$version, 'tags')
                      ? $pkgjson->$pkg->metadata->$version->tags
                      : null);
            }
        }
    }
}
if ($pkgcount > 0) {
    echo "Done!\n";
}

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

// Get the list of packages in the database to see if
// we need to delete ones not in the bro-pkg listing.
$datapkgs = array();
$stmt = $pdo->prepare('SELECT name FROM packages');
$stmt->execute();
if ($stmt->rowCount() > 0) {
    $datapkgs = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Check if updater is already running
$running = false;
$stmt = $pdo->prepare('SELECT status, started FROM updater;');
$stmt->execute();
if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch();
    if ($row['status'] == 'running') {
        // If updater has been running over an hour, then start anew.
        if ((time() - strtotime($row['started'])) < 3600) {
            $running = true;
        }
    }
} else {
    // First time running
    $stmt = $pdo->prepare("INSERT INTO updater (id,status) VALUES(1,'idle');");
    $stmt->execute();
}

if ($running) {
    exit("Error. The updater is already running.\n");
} else {
    // Write 'running' for updater status
    $stmt = $pdo->prepare("UPDATE updater SET status='running', " .
        "started=now(), package=NULL WHERE id=1;");
    $stmt->execute();
}

// Scan through the previously populated $pkgs array and 
// write the package info to the database.

$pkgidx = 0;
$pkgcount = count($pkgs);
foreach ($pkgs as $pkgname => $pkginfo) {
    $pkgidx += 1;

    // Write the currently processed package to the database
    $stmt = $pdo->prepare("UPDATE updater SET package=:pkgname WHERE id=1;");
    $stmt->execute(['pkgname' => "$pkgname ($pkgidx of $pkgcount)"]);

    // Remove the currently processed package from the list of
    // database packages. What is left at the end is extra to be deleted.
    if (($idx = array_search($pkgname, $datapkgs)) !== false) {
        unset($datapkgs[$idx]);
    }

    // Extract pacakge author and short_name from pkgname
    $parts = explode("/", $pkgname);
    $pkgauthor = $parts[1];
    $pkgshort = $parts[2];

    // Get the database ID for the package (if any)
    $pkgid = '';
    $stmt = $pdo->prepare("SELECT id FROM packages WHERE name=:pkgname;");
    $stmt->execute(['pkgname' => $pkgname]);
    if ($stmt->rowCount() > 0) { // Already in the database, so update it.
        echo "Updating package '$pkgname' ($pkgidx of $pkgcount)\n";
        $row = $stmt->fetch();
        $pkgid = $row['id'];
        $stmt = $pdo->prepare("UPDATE packages SET " .
            "author=:pkgauthor, " .
            "short_name=:pkgshort, " .
            "url=:pkgurl, " .
            "readme=:readme, " .
            "readme_name=:readme_name, " .
            "subscribers_count=:subscribers_count, " .
            "stargazers_count=:stargazers_count, " .
            "open_issues_count=:open_issues_count, " .
            "forks_count=:forks_count, " .
            "pushed_at=:pushed_at, " .
            "modified=now() " .
            "WHERE name=:pkgname;");

        $stmt->execute([
            'pkgname'           => $pkgname,
            'pkgauthor'         => $pkgauthor,
            'pkgshort'          => $pkgshort,
            'pkgurl'            => $pkginfo['url'],
            'readme'            => $pkginfo['readme'],
            'readme_name'       => $pkginfo['readme_name'],
            'subscribers_count' => $pkginfo['subscribers_count'],
            'stargazers_count'  => $pkginfo['stargazers_count'],
            'open_issues_count' => $pkginfo['open_issues_count'],
            'forks_count'       => $pkginfo['forks_count'],
            'pushed_at'         => $pkginfo['pushed_at']
        ]);
    } else { // Package doesn't exist in the database. Insert it and get ID.
        echo "Adding package '$pkgname' ($pkgidx of $pkgcount)\n";
        $stmt = $pdo->prepare("INSERT INTO packages " .
            "VALUES(uuid(), :pkgname, :pkgauthor, :pkgshort, :pkgurl, " .
            ":readme, :readme_name, :subscribers_count, :stargazers_count, " .
            ":open_issues_count, :forks_count, :pushed_at, now(), now());");
        $stmt->execute([
            'pkgname'           => $pkgname,
            'pkgauthor'         => $pkgauthor,
            'pkgshort'          => $pkgshort,
            'pkgurl'            => $pkginfo['url'],
            'readme'            => $pkginfo['readme'],
            'readme_name'       => $pkginfo['readme_name'],
            'subscribers_count' => $pkginfo['subscribers_count'],
            'stargazers_count'  => $pkginfo['stargazers_count'],
            'open_issues_count' => $pkginfo['open_issues_count'],
            'forks_count'       => $pkginfo['forks_count'],
            'pushed_at'         => $pkginfo['pushed_at']
        ]);
        $stmt = $pdo->prepare("SELECT id FROM packages WHERE name=:pkgname;");
        $stmt->execute(['pkgname' => $pkgname]);
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            $pkgid = $row['id'];
        } else {
            $pkgid = '';
        }
    }
    if (empty($pkgid)) {
        exit("Error. Could not get ID for '$pkgname'.\n");
    }

    // Process all versions of metadatas for the package
    if (array_key_exists('metadata', $pkginfo)) {
        foreach ($pkginfo['metadata'] as $version => $verinfo) {
            // Get the database ID for the metadata version (if any)
            $metaid = '';
            $stmt = $pdo->prepare("SELECT id FROM metadatas WHERE package_id=:pkgid and version=:version;");
            $stmt->execute(['pkgid' => $pkgid, 'version' => $version]);
            if ($stmt->rowCount() > 0) { // Already in the database, so update it.
                echo "    Updating metadata version '$version'.\n";
                $row = $stmt->fetch();
                $metaid = $row['id'];
                $stmt = $pdo->prepare(
                    "UPDATE metadatas SET " .
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
                    'description' => $verinfo['description'],
                    'script_dir' => $verinfo['script_dir'],
                    'plugin_dir' => $verinfo['plugin_dir'],
                    'build_command' => $verinfo['build_command'],
                    'user_vars' => $verinfo['user_vars'],
                    'test_command' => $verinfo['test_command'],
                    'config_files' => $verinfo['config_files'],
                    'depends' => $verinfo['depends'],
                    'external_depends' => $verinfo['external_depends'],
                    'suggests' => $verinfo['suggests'],
                    'metaid' => $metaid
                ]);
            } else { // Package doesn't exist in the database. Insert it and get ID.
                echo "    Adding metadata version '$version'.\n";
                $stmt = $pdo->prepare(
                    "INSERT INTO metadatas " .
                    "VALUES(uuid(), :pkgid, :version, " .
                    ":description, :script_dir, :plugin_dir, :build_command, " .
                    ":user_vars, :test_command, :config_files, " .
                    ":depends, :external_depends, :suggests, now(), now());"
                );
                $stmt->execute([
                    'pkgid' => $pkgid,
                    'version' => $version,
                    'description' => $verinfo['description'],
                    'script_dir' => $verinfo['script_dir'],
                    'plugin_dir' => $verinfo['plugin_dir'],
                    'build_command' => $verinfo['build_command'],
                    'user_vars' => $verinfo['user_vars'],
                    'test_command' => $verinfo['test_command'],
                    'config_files' => $verinfo['config_files'],
                    'depends' => $verinfo['depends'],
                    'external_depends' => $verinfo['external_depends'],
                    'suggests' => $verinfo['suggests']
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
            if (!is_null($verinfo['tags']) && (strlen($verinfo['tags']) > 0)) {
                // Split tags on commas
                $tagsarray = array_map('trim', explode(',', $verinfo['tags']));
                foreach ($tagsarray as $tag) {
                    // Get the database ID for the tag (if any)
                    $tagid = '';
                    $stmt = $pdo->prepare("SELECT id FROM tags WHERE name=:tag;");
                    $stmt->execute(['tag' => $tag]);
                    if ($stmt->rowCount() > 0) {
                        echo "        Updating tag '$tag'\n";
                        $row = $stmt->fetch();
                        $tagid = $row['id'];
                    } else { // Tag doesn't exist. Insert it and get ID.
                        echo "        Adding tag '$tag'\n";
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

                    // Link the tag to the metadata version
                    $stmt = $pdo->prepare("INSERT IGNORE INTO metadatas_tags " .
                        "VALUES(:metaid, :tagid);");
                    $stmt->execute(['metaid' => $metaid, 'tagid' => $tagid]);
                }
            }
        }
    }
}

// If there are any remaining packages in the original $datapkgs array,
// then these were not found in the current bro-pkg listing and
// should be deleted from the database.
if (count($datapkgs) > 0) {
    foreach ($datapkgs as $pkgname) {
        echo "Deleting $pkgname from database.\n";
        $stmt = $pdo->prepare('DELETE FROM packages WHERE name=:pkgname');
        $stmt->execute(['pkgname' => $pkgname]);
    }
}

// Write 'idle' for updater status
$stmt = $pdo->prepare("UPDATE updater SET status='idle', ended=now(), package=NULL WHERE id=1;");
$stmt->execute();

function objToStr($obj)
{
    $retval = null;
    if (!is_null($obj)) {
        if (is_object($obj)) {
            $keys = array_keys(get_object_vars($obj));
            $str = '';
            foreach ($keys as $key) {
                $str .= $key . ' ' . $obj->$key . "\n";
            }
        } else { // Just a string
            $str = $obj;
        }
        $retval = trim($str);
    }
    return $retval;
}

function searchEnvFile($envvalue, $envlines)
{
    $retval = '';
    $linematch = @array_values(preg_grep('/export ' . $envvalue . '\s*=\s*".*"/', $envlines))[0];
    if (preg_match('/export ' . $envvalue . '\s*=\s*"(.*)"/', $linematch, $matches)) {
        $retval = $matches[1];
    }
    if (strlen($retval) == 0) {
        exit("Error. Unable to read $envvalue from .env file.\n");
    }

    return $retval;
}

?>
