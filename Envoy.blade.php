@servers(['web' => 'root@labrary.org'])

@setup
    $now          = new DateTime();
    $release      = $now->format('YmdHis');
    $release_year = $now->format('Y');

    $repository = 'git@github.com:labrary/labrary.org.git';
    $branch     = 'master';

    $root_directory    = '/var/www/labrary.org/www';
    $release_directory = $root_directory . '/' . $release;
    $current_directory = $root_directory . '/current';

    $keep_releases = 5;
@endsetup

@task('fetch_repository')
    echo "Fetch repository {{ $repository }}:{{ $branch }} to {{ $release }}";
    [ -d {{ $root_directory }} ] || mkdir {{ $root_directory }};
    cd {{ $root_directory }};
    git clone -b {{ $branch }} {{ $repository }} {{ $release }};
@endtask

@task('update_permissions')
    echo "Update permissions";
    cd {{ $root_directory }};
    chown -R www-data:www-data {{ $release }};
@endtask

@task('update_symlinks')
    echo "Update symlinks";
    ln -nfs {{ $release_directory }} {{ $current_directory }};
    chown -h www-data {{ $current_directory }};
@endtask

@task('cleanup_old_releases')
    echo "Clean up old releases";
    cd {{ $root_directory }};
    ls -1d {{ $release_year }}* | head -n -{{ $keep_releases }} | xargs rm -Rf;
@endtask

@macro('deploy', ['on' => 'web'])
    fetch_repository
    update_permissions
    update_symlinks
    cleanup_old_releases
@endmacro
