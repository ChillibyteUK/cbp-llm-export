# CBP LLMS Exporter

Export eligible WordPress posts and pages in markdown syntax to `llms.txt` for LLMs and other tools.

## Features
- Admin page under **Tools** to export post/page links in markdown format
- Select which post types to include
- Add a one-line site summary (blockquote) for LLM context
- Excludes posts with Yoast SEO noindex flag
- Output written to `.well-known/llms.txt`
- Provides redirect setup instructions for compatibility across hosting platforms
- Remembers selections and summary for future exports

## Usage
1. Go to **Tools > LLMS Export** in the WordPress admin.
2. Enter a one-line summary describing your site (for LLMs).
3. Select the post types you want to include.
4. Click **Export to llms.txt**.
5. The file will be created at `.well-known/llms.txt` in your WordPress root directory.

## Output Format
```
# Site Title
> Site summary

## Pages
- [Page Title](https://yoursite.com/page-url)

## Posts
- [Post Title](https://yoursite.com/post-url)
```

## Requirements
- WordPress 5.0+
- PHP 7.0+

## Security
- Uses WordPress nonces for form security
- Only users with `manage_options` capability can export

## License
MIT

## Changelog

### 1.1
- Changed output location from root to `.well-known/llms.txt` (following web standards)
- Added redirect setup instructions for Redirection plugin or WP Engine User Portal
- Detects WP Engine hosting and shows appropriate redirect instructions
- Added admin notice if old `llms.txt` file exists in root
- Automatically creates `.well-known` directory if it doesn't exist
- Checks if `.well-known/llms.txt` is accessible via HTTP
- Fixed PHPCS lint warnings

### 1.0
- Initial release
- Admin page for exporting posts/pages
- Configurable post type selection
- Site summary field
- Yoast SEO noindex detection
- Markdown formatted output
