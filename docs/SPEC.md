# wp-custom-blocks — Specification

Status: draft v1 · Type: study project (public)

## 1. Goal

A small WordPress plugin with three custom Gutenberg blocks. Each block demonstrates a different skill:

| Block | Type | Demonstrates |
|---|---|---|
| `af-blocks/callout` | Static | `block.json`, attributes, `RichText`, `InspectorControls`, block supports |
| `af-blocks/faq` + `af-blocks/faq-item` | Parent/child, PHP render | `InnerBlocks`, native accessible markup, `FAQPage` JSON-LD |
| `af-blocks/related-posts` | Dynamic | `render.php`, `WP_Query` tuning, transient cache with invalidation, escaping |

### Non-goals

- Interactivity API, block patterns, block variations
- Publishing on WordPress.org
- PHPUnit/E2E tests (listed as a next step in the README)

## 2. Requirements

- WordPress 6.6+ · PHP 8.1+ · Node 20+ · Composer 2
  *(6.6, not 6.5: current `@wordpress/scripts` compiles JSX against the `react-jsx-runtime` script, which WordPress ships from 6.6.)*
- `@wordpress/scripts` (build, lint) · `@wordpress/env` (local WordPress in Docker)
- `@wordpress/*` packages imported by the blocks (`blocks`, `block-editor`, `components`, `i18n`, and later `server-side-render`) are devDependencies. They are not bundled (the build maps them to the `wp.*` globals); they are listed so the default `lint:js` can resolve imports.
- PHPCS with WordPress Coding Standards 3.x

## 3. Structure

```
wp-custom-blocks/
├── af-blocks.php              plugin header + bootstrap
├── includes/
│   ├── register-blocks.php    registers every block from build/
│   └── related-cache.php      cache version + invalidation hooks
├── src/
│   ├── callout/               block.json, index.js, edit.js, save.js, style.scss, editor.scss
│   ├── faq/                   block.json, index.js, edit.js, render.php, style.scss
│   ├── faq-item/              block.json, index.js, edit.js, save.js
│   └── related-posts/         block.json, index.js, edit.js, render.php, style.scss
├── build/                     generated, committed for zip installs (see §7)
├── uninstall.php
├── docs/SPEC.md
├── AGENTS.md
├── .github/workflows/ci.yml
├── .wp-env.json · composer.json · package.json · phpcs.xml.dist · .gitignore
├── LICENSE (GPL-2.0-or-later)
└── README.md
```

## 4. Conventions (all blocks)

- **Namespace** `af-blocks/` · **PHP prefix** `af_blocks_` · **text domain** `af-blocks`
- `block.json` uses `"apiVersion": 3`. Every block is registered from metadata (`register_block_type( __DIR__ . '/build/<block>' )`).
- **Escape late, sanitize early.** Every value printed in PHP goes through `esc_html`, `esc_attr`, `esc_url` or `wp_kses_post` at the point of output.
- Every user-facing string is translatable (`__()`, `esc_html__()`, and `@wordpress/i18n` in JS).
- **No front-end JavaScript** unless a block cannot work without it. None of the three blocks needs any.
- **Empty means nothing.** A block with no meaningful content renders no markup (no empty wrappers).
- **Every PHP file** starts with `defined( 'ABSPATH' ) || exit;`.

## 5. Blocks

### 5.1 `af-blocks/callout` (static)

**Attributes**

| Name | Type | Default | Notes |
|---|---|---|---|
| `variant` | string, enum `info` · `success` · `warning` · `danger` | `info` | Chosen in `InspectorControls` |
| `title` | string (rich text) | `""` | Optional |
| `content` | string (rich text) | `""` | Required for output |

**Supports:** `color` (background, text), `spacing` (padding, margin), `typography.fontSize`, `align` (`wide`).

**Output**

```html
<aside class="wp-block-af-blocks-callout is-variant-info" role="note">
  <p class="wp-block-af-blocks-callout__title">…</p>   <!-- only if title -->
  <div class="wp-block-af-blocks-callout__content">…</div>
</aside>
```

**Acceptance**

- [x] The variant changes the class only. Colors come from CSS custom properties, with no inline styles from the variant.
- [x] Without a title, there is no title element.
- [x] Changing `save.js` markup after release requires a `deprecated` entry. The README explains why: static blocks are validated against saved markup.

### 5.2 `af-blocks/faq` + `af-blocks/faq-item`

**`faq-item` (child, static)**

- `parent: ["af-blocks/faq"]`
- Attribute: `question` (string, rich text), stored in the block comment so `render.php` can read it
- Always saves its markup, even with an empty question: a static block that saves nothing also drops its inner blocks, so the answer would be lost
- Answer: `InnerBlocks`, allowed `core/paragraph` and `core/list`
- Output:

```html
<details class="wp-block-af-blocks-faq-item">
  <summary>Question</summary>
  <div class="wp-block-af-blocks-faq-item__answer"><!-- inner blocks --></div>
</details>
```

**`faq` (parent, PHP render)**

- `InnerBlocks` with `allowedBlocks: ["af-blocks/faq-item"]` and a template of 2 empty items
- Attribute: `outputSchema` (boolean, default `true`)
- `render.php` prints the wrapper and renders the items one by one, skipping any item with an empty question. If no item has a question, it renders nothing. When `outputSchema` is true, it adds a `<script type="application/ld+json">` with `FAQPage`, built from each child's `question` and its rendered answer as plain text (`wp_strip_all_tags`).

**Acceptance**

- [x] Keyboard and screen readers work through native `<details>`/`<summary>`, with no JS.
- [x] Only **one** `FAQPage` per page: if several FAQ blocks exist, only the first prints schema (static flag in PHP).
- [x] Items with an empty question or an empty answer are skipped in the schema.
- [ ] The JSON is printed with `wp_json_encode()` and validates in the Rich Results Test. *(Printed with `wp_json_encode()`; validator.schema.org reports 0 errors and 0 warnings. Rich Results Test still to run: it requires a Google sign-in.)*

### 5.3 `af-blocks/related-posts` (dynamic)

**Attributes**

| Name | Type | Default | Notes |
|---|---|---|---|
| `count` | integer | `3` | Clamped 1–6 in PHP, not only in the UI |
| `taxonomy` | string, enum `category` · `post_tag` | `category` | |
| `showImage` | boolean | `true` | |
| `showDate` | boolean | `false` | |

**Editor:** `ServerSideRender` preview plus `InspectorControls`.

**Query (`render.php`)**

- Runs only on a singular post (`is_singular( 'post' )`), plus the editor's `ServerSideRender` request (`/wp/v2/block-renderer/af-blocks/related-posts`) for a post, so the preview works. Anywhere else, including other REST responses such as `content.rendered`, it renders nothing.
- Uses the terms of the current post in `taxonomy`. If there are no terms, it renders nothing.
- `WP_Query` args: `post__not_in` (current post), `posts_per_page` = `count`, `no_found_rows => true`, `ignore_sticky_posts => true`, `update_post_term_cache => false`. `update_post_meta_cache` is true only when `showImage` is on.

**Cache**

- The result (post IDs only) is stored in a transient for 12 hours.
- The key includes the post ID, a hash of the attributes that change the result (`taxonomy`, `count`) and a **cache version** option. `showImage` and `showDate` only change the output, so they do not split the cache.
- The version option is created on activation with autoload on, so reading it costs no query.
- `save_post`, `deleted_post` and `set_object_terms` bump the version, for posts of type `post` only (revisions and autosaves are ignored; `set_object_terms` only for `category` and `post_tag`). Old keys are never deleted one by one: they become unreachable and expire. *(This decision goes in the README: invalidation without tracking keys.)*

**Output:** `<ul>` of linked titles, an optional image via `wp_get_attachment_image()` (lazy, with real `sizes`) and an optional `<time datetime="">`.

**Acceptance**

- [x] A second page view runs no related-posts `WP_Query` (no taxonomy query). What remains is the transient read and primary-key loads of the cached posts (and their images). Without a persistent object cache these still hit the database; with one, they do not. Verify with Query Monitor or `SAVEQUERIES`.
- [x] Editing any post shows fresh results on the next view.
- [x] A malicious title (`<script>`) prints escaped.

## 6. Uninstall

`uninstall.php` deletes the cache version option. Transients expire on their own.

## 7. Quality gates

| Command | Must pass |
|---|---|
| `npm run build` | Builds every block to `build/` |
| `npm run lint:js` · `npm run lint:css` | `@wordpress/scripts` defaults |
| `composer phpcs` | WordPress Coding Standards, zero errors |
| `npm run env:start` | Plugin activates without notices (`WP_DEBUG` on) |

**CI** (`.github/workflows/ci.yml`, on push and pull request): `composer install` → `composer phpcs`, then `npm ci` → `npm run build` → lint.

`build/` is committed so the repo works as a zip install. CI fails if `build/` is out of date (`git diff --exit-code build/`).

## 8. Definition of done

- [ ] All acceptance criteria in §5 checked
- [x] All gates in §7 green, and CI green on `main`
- [x] README in English: problem, the 3 blocks with a screenshot each, decisions and trade-offs, how to run (`wp-env`), limitations, next steps
- [x] Commits are small and describe the change (one block or one concern per commit)

## 9. Build order

1. Scaffold, tooling, CI, PHPCS green on an empty plugin
2. Callout
3. FAQ + FAQ item
4. Related Posts + cache
5. README + screenshots
