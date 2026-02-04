# IAR Basic Setup - TODO

## 1. GitHub Setup

- [ ] Create GitHub repository (e.g., `iar-basic-setup`)
- [ ] Add `.gitignore`:
  ```
  .DS_Store
  node_modules/
  *.log
  ```
- [ ] Initial commit and push

## 2. WordPress.org Submission

- [ ] Create account on wordpress.org (if you don't have one)
- [ ] Submit plugin: https://wordpress.org/plugins/developers/add/
- [ ] Upload ZIP (without .git directory)
- [ ] Wait for approval (can take 1-14 days)
- [ ] After approval, you'll receive SVN access

## 3. Assets for WordPress.org (optional but recommended)

- [ ] Create `assets/banner-772x250.png` (header banner)
- [ ] Create `assets/banner-1544x500.png` (retina banner)
- [ ] Create `assets/icon-128x128.png` (icon)
- [ ] Create `assets/icon-256x256.png` (retina icon)
- [ ] Take screenshot(s) of admin interface

## 4. GitHub Actions - Auto Deploy to SVN

After receiving SVN access from WordPress.org:

- [ ] Add GitHub Secrets:
  - `SVN_USERNAME` - your wordpress.org username
  - `SVN_PASSWORD` - your wordpress.org password

- [ ] Create `.github/workflows/deploy.yml`:
  ```yaml
  name: Deploy to WordPress.org

  on:
    release:
      types: [published]

  jobs:
    deploy:
      runs-on: ubuntu-latest
      steps:
        - uses: actions/checkout@v4

        - name: WordPress Plugin Deploy
          uses: 10up/action-wordpress-plugin-deploy@stable
          env:
            SVN_USERNAME: ${{ secrets.SVN_USERNAME }}
            SVN_PASSWORD: ${{ secrets.SVN_PASSWORD }}
            SLUG: iar-basic-setup
  ```

- [ ] For assets, add to workflow:
  ```yaml
          with:
            generate-zip: true
          env:
            SVN_USERNAME: ${{ secrets.SVN_USERNAME }}
            SVN_PASSWORD: ${{ secrets.SVN_PASSWORD }}
            SLUG: iar-basic-setup
            ASSETS_DIR: .wordpress-org
  ```

- [ ] Create `.wordpress-org/` directory for SVN assets (banner, icon)

## 5. Versioning and Release

When you want to publish a new version:

1. Update version in:
   - `iar-basic-setup.php` (Version: x.x.x)
   - `readme.txt` (Stable tag: x.x.x)
2. Update Changelog in `readme.txt`
3. Commit and push to GitHub
4. Create GitHub Release with tag (e.g., `v1.0.1`)
5. GitHub Action will automatically deploy to WordPress.org SVN

## 6. Post-Launch

- [ ] Monitor support forum: https://wordpress.org/support/plugin/iar-basic-setup
- [ ] Respond to reviews
- [ ] Regularly test with new WP versions
- [ ] Update "Tested up to" in readme.txt

## 7. Future Module Ideas

- [ ] Disable RSS Feeds - Disables all RSS/Atom feeds
- [ ] Disable REST API for Guests - Restricts REST API access to authenticated users only
- [ ] Limit Login Attempts - Blocks IP after X failed login attempts
- [ ] Disable Author Archives - Prevents user enumeration through author archive pages
- [ ] Maintenance Mode - Shows a coming soon/maintenance page for non-admin visitors
- [ ] Custom Login Logo - Replaces the WordPress logo on the login page
- [ ] Disable Auto Updates - Controls automatic updates for core, plugins, and themes
- [ ] SMTP Mail - Sends emails through SMTP instead of PHP mail()
- [ ] Custom Login URL - Replaces /wp-login.php with a custom path to reduce brute-force attacks

---

## Notes

- SVN structure used by WordPress.org:
  ```
  /assets/      <- banner, icon, screenshots (NOT included in plugin ZIP)
  /tags/        <- each version
  /trunk/       <- development version
  ```

- GitHub Action handles this structure automatically

- `.distignore` file (optional) - to exclude files from deployment:
  ```
  .git
  .github
  .gitignore
  TODO.md
  ```
