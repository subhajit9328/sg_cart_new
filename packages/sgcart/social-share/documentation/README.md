# SGCart Social Share & Influencer System Package

The `sgcart/social-share` package introduces an interactive storefront style feed inspired by **Myntra Studio**. It enables customers to showcase clothing and product purchases through high-fidelity images and video reels. It verifies posts using order numbers or product SKUs, includes a moderation panel for store admins, and builds a social ecosystem where users can follow influencers and view follower stats.

---

## Features

1. **Storefront Studio Feed**:
   - High-fidelity grid layout exhibiting customer-shared images and video reels.
   - Elegant media preview lightbox (modal player supporting auto-playback of videos/reels).
   - In-card interactive follow toggles and product shop links.

2. **Customer Social Hub (Account Section)**:
   - Dynamic tab selector "Social Share" in customer profiles.
   - Upload form for reels and images with caption text.
   - Smart verification requiring either a valid **Order ID / Order Number** or a **Product SKU**.
   - Influencer Stats Dashboard displaying followers count and lists of followed users/followers.
   - History list of customer-submitted posts with status indicators (Pending, Approved, Rejected with admin notes).

3. **Admin Moderation Panel**:
   - Dedicated moderation queue for store admins.
   - Visual media preview lightbox.
   - Order/SKU verification validation context.
   - Dynamic approval or rejection actions (with input reasons for rejections).

---

## Database Architecture

The package programmatically runs and maintains two custom tables:

### 1. `social_posts`
Stores influencer media uploads, linked validation tokens, captions, and approval states.
- `id` (Primary Key)
- `customer_id` (Foreign Key -> `customers.id`)
- `media_path` (Storage path string)
- `media_type` ('image' or 'video')
- `order_number` (String, validation context)
- `product_sku` (String, validation context)
- `product_id` (Foreign Key -> `products.id`, nullable)
- `caption` (Text content)
- `status` (Enum: `pending`, `approved`, `rejected`)
- `rejection_reason` (Text content)
- `approved_by` (Foreign Key -> `users.id`, nullable)
- `approved_at` (Timestamp)

### 2. `social_follows`
Stores follower-influencer mapping to aggregate follow graphs.
- `id` (Primary Key)
- `follower_id` (Foreign Key -> `customers.id`)
- `influencer_id` (Foreign Key -> `customers.id`)
- `unique` (`follower_id`, `influencer_id`)

---

## Installation & Setup

1. **Include Package Repository**:
   Add the local path package to your root `composer.json` repositories array:
   ```json
   {
       "type": "path",
       "url": "packages/sgcart/social-share",
       "options": {
           "symlink": true
       }
   }
   ```

2. **Register Requirement**:
   Require the package via Composer inside root `composer.json`:
   ```json
   "require": {
       "sgcart/social-share": "@dev"
   }
   ```

3. **Bootstrap Services**:
   Run `composer update sgcart/social-share` or `composer update` to symlink and register autoload namespaces. The `SocialShareServiceProvider` automatically loads migrations, routes, views, and seeds the Spatie permission `manage social shares`.

---

## Routing Reference

### Storefront Routes
- `GET /studio` (`store.social-share.index`): Publicly visible influencer styling feed.
- `POST /studio/post` (`store.social-share.store`): Handles video/image look upload (verified by Order/SKU).
- `POST /studio/follow/{influencerId}` (`store.social-share.follow`): AJAX follow/unfollow toggle.

### Admin Moderation Routes
- `GET /admin/social-shares` (`admin.social-shares.index`): Moderation queue panel.
- `POST /admin/social-shares/{id}/approve` (`admin.social-shares.approve`): Approve post request.
- `POST /admin/social-shares/{id}/reject` (`admin.social-shares.reject`): Reject post request with a reason.
- `DELETE /admin/social-shares/{id}` (`admin.social-shares.destroy`): Delete influencer post.
