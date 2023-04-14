## Contributing ##

The best way to contribute to the development of this plugin is by participating on the GitHub project:

https://github.com/pantheon-systems/wp-decoupled-preview

Pull requests and issues are welcome!

## Workflow

The `develop` branch is the development branch which means it contains the next version to be released. `main` contains the corresponding stable version. Always work on the `develop` branch and open up PRs against `develop`. We'd prefer to squash commits (e.g. avoid merge PRs) from a `feat-branch` into `develop` when merging, including the PR # in the commit message.

## Release Process

1. Starting from `develop` merge changes into `main`. Merge commits are preferred in this case - never squash to `main`.
1. Cut a release branch from `main` named `release_X.Y.Z` containing your changes.
1. Drop the `-dev` from the version number in relevant files, update "Latest" in changelog to the new version number, commit these changes, and push the release branch up.
1. Create a PR from the release branch against `main`.
1. After all tests pass and you have received approval from a CODEOWNER (including resolving any merge conflicts), merge the PR into `main`.
1. Pull `main` locally, create a new tag (based on version number from previous steps), and push up.
1. Create a [new release](https://github.com/pantheon-systems/wp-decoupled-preview/releases/new) using the tag created in the previous steps, naming the release with the new version number, and targeting the tag created in the previous step. Paste the release changelog from the `Changelog` section of the `README` into the body of the release and include a link to the closed issues if applicable.
1. Wait for the [_Release wp-decoupled-preview plugin to wp.org_ action](https://github.com/pantheon-systems/wp-decoupled-preview/actions/workflows/wordpress-plugin-deploy.yml) to finish deploying to the WordPress.org repository. If all goes well, users with SVN commit access for that plugin will receive an emailed diff of changes.
1. Check WordPress.org: Ensure that the changes are live on https://wordpress.org/plugins/decoupled-preview/. This may take a few minutes.
1. Following the release, perform the following steps:
* `git checkout develop`
* `git rebase main`
* Commit a new change to the readme.txt: add "Latest" heading, increment the Stable tag and add `-dev` flag
* `git push origin develop`