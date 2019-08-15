<?php

namespace Deployer;

require 'recipe/common.php';

set('repository', 'git@github.com:xenoidon/deploy-skeleton.git');
set('keep_releases', 5);
add('shared_dirs', ['tmp']);
add('shared_files', ['.env']);
add('writable_dirs', ['tmp', 'storage', 'vendor', 'public/uploads']);
set('writable_use_sudo', false);


task('deploy:start', function () {
    cd('~');
    run("if [ ! -d {{deploy_path}} ]; then mkdir -p {{deploy_path}}; fi");
    cd('{{deploy_path}}');
})->setPrivate();


desc('Make config files for your stage');
task('deploy:config', function () {

    $paser = function ($matches) {
        if (isset($matches[1])) {
            $value = get($matches[1]);
            if (is_null($value) || is_bool($value) || is_array($value)) {
                $value = var_export($value, true);
            }
        } else {
            $value = $matches[0];
        }
        return $value;
    };

    $compiler = function ($contents) use ($paser) {
        $contents = preg_replace_callback('/\{\{\s*([\w\.]+)\s*\}\}/', $paser, $contents);

        return $contents;
    };

    $finder = new \Symfony\Component\Finder\Finder();
    $iterator = $finder
        ->files()
        ->name('*.tpl')
        ->in(__DIR__ . '/shared');
    $tmpDir = sys_get_temp_dir();
    foreach ($iterator as $file) {
        $success = false;
        // Make tmp file
        $tmpFile = tempnam($tmpDir, 'tmp');
        if (!empty($tmpFile)) {
            try {
                $contents = $compiler($file->getContents());
                $target = preg_replace('/\.tpl$/', '', $file->getRelativePathname());
                if (file_put_contents($tmpFile, $contents) > 0) {
                    upload($tmpFile, '{{deploy_path}}/shared/' . $target);
                    $success = true;
                }
            } catch (\Exception $e) {
                $success = false;
            }
            unlink($tmpFile);
        }
        if ($success) {
            writeln(sprintf("<info>✔</info> %s", $file->getRelativePathname()));
        } else {
            writeln(sprintf("<fg=red>✘</fg=red> %s", $file->getRelativePathname()));
        }
    }
});

/**
 * Main task
 */
task('deploy', [
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:vendors',
    'deploy:clear_paths',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
])->desc('Deploy your project');

before('deploy:config', 'deploy:start');
after('deploy:failed', 'deploy:unlock');
after('deploy:shared', 'deploy:writable');
before('deploy', 'deploy:start');
after('deploy', 'success');
