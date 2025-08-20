# CBP LLMS Exporter

Export eligible WordPress posts and pages in markdown syntax to `llms.txt` for LLMs and other tools.

## Features
- Admin page under **Tools** to export post/page links in markdown format
- Select which post types to include
- Add a one-line site summary (blockquote) for LLM context
- Excludes posts with Yoast SEO noindex flag
- Output written to the WordPress root as `llms.txt`
- Remembers selections and summary for future exports

## Usage
1. Go to **Tools > LLMS Export** in the WordPress admin.
2. Enter a one-line summary describing your site (for LLMs).
3. Select the post types you want to include.
4. Click **Export to llms.txt**.
5. The file will be created in your WordPress root directory.

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
