#!/usr/bin/env php
<?php

// This script should be added to root's nightly cron to read the
// current list of Zeek packages using the zkg command line utility.

// REQUIRED: Set the location of the bro-pkg-web .env file
// NOTE: This script should be run by user with read access to the .env file.
$envfile = '/var/www/html/config/.env';

// Set the location of the zkg command line exec
$zkg_exec = '/usr/local/bin/zkg';

function fatal($msg) {
    echo "$msg\n";
    exit(1);
}

function timeout_exec($cmd, &$output, &$retval, $timeout=120) {
    $timeout_cmd = "timeout $timeout $cmd";
    exec($timeout_cmd, $output, $retval);

    if ($retval == 124) {
        $output = ["exec($cmd) timed out after $timeout seconds"];
    }
}

function required_exec($cmd, $timeout=120) {
    $output=null;
    $retval=null;
    timeout_exec($cmd, $output, $retval, $timeout);
    if ($retval != 0) {
        echo "exec($cmd) failed with status $retval and output:\n";
        print_r($output);
        exit(1);
    }
    return $output;
}

// Read .env file and scan for important secrets
if (($envlines = file($envfile)) === false) {
    fatal("Error. Unable to read $envfile .");
}
$githubtoken = searchEnvFile('GITHUBTOKEN', $envlines);
$sqldb       = searchEnvFile('DB_DATABASE', $envlines);
$sqlhost     = searchEnvFile('DB_HOST',     $envlines);
$sqluser     = searchEnvFile('DB_USERNAME', $envlines);
$sqlpass     = searchEnvFile('DB_PASSWORD', $envlines);

// Read all info from zkg into local variable $pkgs to
// make database updating as fast as possible.
$pkgs = array();

// Refresh the local bro package listing.
$refresh = required_exec("$zkg_exec refresh");

// Get a list of all bro packages
$pkgarray = required_exec("$zkg_exec list all --nodesc");

$zkg_scratch_dir = required_exec("$zkg_exec config state_dir")[0];
$zkg_scratch_dir .= "/scratch";

$pkgcount = count($pkgarray);
if ($pkgcount > 0) {
    echo "Processing $pkgcount packages\n\n";
}
foreach ($pkgarray as $pkg) {
    echo "Processing $pkg\n";

    // Remove trailing 'local package' information
    $pkg = preg_replace('/\s.*$/', '', $pkg);

    // Get package info for the current package
    $zkg_output = null;
    $zkg_res = null;
    timeout_exec("$zkg_exec info $pkg --json --nolocal --allvers", $zkg_output, $zkg_res);

    if ($zkg_res != 0) {
        echo "\nError.  Failed to get info for '$pkg'. Skipping.  zkg output was:\n";
        print_r($zkg_output);
        continue;
    }

    $pkginfo = implode("\n", $zkg_output);
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
        // Remove trailing '.git'
        $pkgurl = preg_replace('/\.git$/', '', $pkgjson->$pkg->url);
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
                if (is_null($json)) {
                    echo "Warning: failed to decode json\n";
                }
                else {
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
        }
        curl_close($ch);
    }

    $parts = explode("/", $pkgs[$pkg]['url']);
    $pkgshort = end($parts);

    // Get all versions of metadatas for the package
    if (property_exists($pkgjson->$pkg, 'metadata')) {
        $versions = array_keys(get_object_vars($pkgjson->$pkg->metadata));

        // Clone the code from github to run bro-package-check
        // on each metadata branch version
        $tempdir = mkTempDir();
        $pkgdir = $tempdir . '/' . $pkgshort;
        $chdirok = chdir($tempdir);
        $output = '';
        if ($chdirok) {
            @exec('git clone ' . $pkgs[$pkg]['url'] . '.git 2>&1', $output);
        }

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
                $pkgs[$pkg]['metadata'][$version]['package_ci'] =
                    runBroPackageCI($pkgdir, $pkgshort, $version);
            }
        }

        if ($chdirok) {
            chdir(sys_get_temp_dir());
            deleteDir($tempdir);
        }
    }

    // Delete the scratch directory for this package so the disk doesn't fill up
    deleteDir("$zkg_scratch_dir/$pkgshort");
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
    fatal('Error. Database Connection Failed: ' . $e->getMessage());
}

// Get the list of packages, metadatas, and tags in the database to see if
// if we need to delete ones not in the zkg listing.
$packages_names = array();
$stmt = $pdo->prepare('SELECT name FROM packages');
$stmt->execute();
if ($stmt->rowCount() > 0) {
    $packages_names = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
$metadatas_ids = array();
$stmt = $pdo->prepare('SELECT id FROM metadatas');
$stmt->execute();
if ($stmt->rowCount() > 0) {
    $metadatas_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
$tags_ids = array();
$stmt = $pdo->prepare('SELECT id FROM tags');
$stmt->execute();
if ($stmt->rowCount() > 0) {
    $tags_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
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
    fatal("Error. The updater is already running.");
} else {
    // Write 'running' for updater status
    $stmt = $pdo->prepare("UPDATE updater SET status='running', " .
        "package=NULL, started=now() WHERE id=1;");
    $stmt->execute();
}

// Scan through the previously populated $pkgs array and
// write the package info to the database.

$pkgidx = 0;
$pkgcount = count($pkgs);
foreach ($pkgs as $pkgname => $pkginfo) {
    // Keep track of count of current package
    $pkgidx += 1;

    // Remove trailing '.git' from $pkgname
    $pkgname = preg_replace('/\.git/', '', $pkgname);

    // Remove the currently processed package from the list of
    // database packages. What is left at the end is extra to be deleted.
    if (($idx = array_search($pkgname, $packages_names)) !== false) {
        unset($packages_names[$idx]);
    }

    // Write the currently processed package to the database
    $stmt = $pdo->prepare("UPDATE updater SET package=:pkgname WHERE id=1;");
    $stmt->execute(['pkgname' => "$pkgname ($pkgidx of $pkgcount)"]);

    // Extract package author and short_name from pkgname
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
        fatal("Error. Could not get ID for '$pkgname'.");
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
                    "package_ci=:package_ci, " .
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
                    'package_ci' => $verinfo['package_ci'],
                    'metaid' => $metaid
                ]);
            } else { // Package doesn't exist in the database. Insert it and get ID.
                echo "    Adding metadata version '$version'.\n";
                $stmt = $pdo->prepare(
                    "INSERT INTO metadatas " .
                    "VALUES(uuid(), :pkgid, :version, " .
                    ":description, :script_dir, :plugin_dir, :build_command, " .
                    ":user_vars, :test_command, :config_files, " .
                    ":depends, :external_depends, :suggests, :package_ci, " .
                    "now(), now());"
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
                    'suggests' => $verinfo['suggests'],
                    'package_ci' => $verinfo['package_ci']
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
                fatal("Error. Could not get ID for version '$version'.");
            }

            // Remove the currently processed metadata from the list of
            // database metadatas. What is left at the end is extra to be deleted.
            if (($idx = array_search($metaid, $metadatas_ids)) !== false) {
                unset($metadatas_ids[$idx]);
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
                        fatal("Error. Could not get ID for tag '$tag'.");
                    }

                    // Link the tag to the metadata version
                    $stmt = $pdo->prepare("INSERT IGNORE INTO metadatas_tags " .
                        "VALUES(:metaid, :tagid);");
                    $stmt->execute(['metaid' => $metaid, 'tagid' => $tagid]);

                    // Remove the currently processed tag from the list of
                    // database tags. What is left at the end is extra to be deleted.
                    if (($idx = array_search($tagid, $tags_ids)) !== false) {
                        unset($tags_ids[$idx]);
                    }
                }
            }
        }
    }
}

// If there are any remaining items in the $packages_names, $metadatas_ids,
// or $tags_ids arrays, then these were not found in the current zkg
// output and should be deleted from the database.
if (count($packages_names) > 0) {
    foreach ($packages_names as $pkgname) {
        echo "Deleting $pkgname from database.\n";
        $stmt = $pdo->prepare('DELETE FROM packages WHERE name=:pkgname');
        $stmt->execute(['pkgname' => $pkgname]);
    }
}
if (count($metadatas_ids) > 0) {
    foreach ($metadatas_ids as $metaid) {
        echo "Deleting metadata $metaid from database.\n";
        $stmt = $pdo->prepare('DELETE FROM metadatas WHERE id=:metaid');
        $stmt->execute(['metaid' => $metaid]);
    }
}
if (count($tags_ids) > 0) {
    foreach ($tags_ids as $tagid) {
        echo "Deleting tag $tagid from database.\n";
        $stmt = $pdo->prepare('DELETE FROM tags WHERE id=:tagid');
        $stmt->execute(['tagid' => $tagid]);
    }
}

// Write 'idle' for updater status
$stmt = $pdo->prepare("UPDATE updater SET status='idle', ended=now(), package=NULL WHERE id=1;");
$stmt->execute();

/**
 * objToStr
 *
 * This function attempts to turn an object into a string. If the input
 * is a string, then just return it unchanged.
 *
 * @param object/string $obj
 * @return string The object transformed into a string.
 */
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

/**
 * searchEnvFile
 *
 * This function searches for an environment variable $envvalue within a
 * bunch of lines $envlines read from a CakePHP .env file. If found, this
 * function returns the parameter. Otherwise, it exits with error.
 *
 * @param string $envvalue The environment variable name to search for.
 * @param string $envlines The CakePHP .env file concatenated into one big string.
 * @return string The value of the environment variable searched for.
 */
function searchEnvFile($envvalue, $envlines)
{
    $retval = '';
    $linematch = @array_values(preg_grep('/export ' . $envvalue . '\s*=\s*".*"/', $envlines))[0];
    if (preg_match('/export ' . $envvalue . '\s*=\s*"(.*)"/', $linematch, $matches)) {
        $retval = $matches[1];
    }
    if (strlen($retval) == 0) {
        fatal("Error. Unable to read $envvalue from .env file.");
    }

    return $retval;
}

/**
 * mkTempDir
 *
 * This function creates a temporary subdirectory within the
 * the system's temp directory. The new directory name is composed of
 * 16 hexadecimal letters, plus any prefix if you specify one. The newly
 * created directory has permissions '0700'. The full path of the the
 * newly created directory is returned.
 *
 * @return string Full path to the newly created temporary directory.
 */
function mkTempDir()
{
    $path = '';
    do {
        $path = sys_get_temp_dir() . '/' .
            sprintf("%08X%08X", mt_rand(), mt_rand());
    } while (!mkdir($path, 0700, true));
    return $path;
}

/**
 * deleteDir
 *
 * This function deletes a directory and all of its contents.
 *
 * @param string $dir The (possibly non-empty) directory to delete.
 * @param bool $shred (Optional) Shred the file before deleting?
 *        Defaults to false.
 */
function deleteDir($dir, $shred = false)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir."/".$object) == "dir") {
                    deleteDir($dir."/".$object);
                } else {
                    if ($shred) {
                        @exec('/bin/env /usr/bin/shred -u -z '.$dir."/".$object);
                    } else {
                        @unlink($dir."/".$object);
                    }
                }
            }
        }
        reset($objects);
        @rmdir($dir);
    }
}

/**
 * runBroPackageCI
 *
 * This function runs the bro-package-check program
 * (https://github.com/bro/bro-package-ci) on a given package
 * directory for a specific version branch.
 *
 * @param string $pkgdir The directory containing the git clone'd
 *        bro package code.
 * @param string $pkgshort The "short" name of the package, e.g. bro-doctor.
 * @param string $version The version branch to git checkout.
 * @return string The result of bro-package-check in JSON format.
 */
function runBroPackageCI($pkgdir, $pkgshort, $version)
{
    $retval = '';
    if (chdir($pkgdir)) {
        $output = '';
        @exec("git checkout $version 2>/dev/null", $output, $return_var);
        if ($return_var == 0) {
            $output = '';
            chdir('..');
            @exec("bro-package-check --json $pkgshort 2>/dev/null", $output, $return_var);
            if ($return_var == 0) {
                $retval = implode($output);
            }
        }
    }
    return $retval;
}

?>
