# REST API Test Cases for Author Profile Fields

This document provides comprehensive test cases for validating the REST API implementation of author profile fields.

## Prerequisites

Before running these tests, ensure:

1. ✅ WordPress is running locally (e.g., via Local by Flywheel)
2. ✅ ACF plugin is installed and activated
3. ✅ Portfolio CMS Functions plugin is activated
4. ✅ At least one user exists with admin privileges
5. ✅ At least one article or project exists (for post-related tests)

## Automated Test Suite

### Running the Test Script

**Option 1: Via Browser**
```
http://yoursite.local/wp-content/plugins/portfolio-cms-functions/test-rest-api.php?run_tests=1
```

**Option 2: Via Command Line**
```bash
cd /path/to/wordpress
php wp-content/plugins/portfolio-cms-functions/test-rest-api.php
```

The automated test suite will verify:
- ✅ All 12 user fields are registered
- ✅ author_profile field is registered for articles and projects
- ✅ Field values can be set and retrieved
- ✅ REST API returns custom fields in responses
- ✅ Author profile data is included in post responses

## Manual Test Cases

### Test Case 1: Setup Author Profile Data

**Steps:**
1. Log into WordPress admin
2. Go to **Users → Your Profile**
3. Scroll down to find the "Author Profile Details" section
4. Fill in the following fields:
   - Author Profile Image: Upload or select an image
   - LinkedIn URL: `https://linkedin.com/in/yourprofile`
   - Twitter URL: `https://twitter.com/yourusername`
   - Instagram URL: `https://instagram.com/yourusername`
   - Facebook URL: `https://facebook.com/yourprofile`
   - YouTube URL: `https://youtube.com/@yourchannel`
   - Web Portfolio URL: `https://yourportfolio.com`
   - Author CTA Hook: `Follow me for more great content!`
   - Author CTA Action URL: `https://yoursite.com/follow`
5. Click **Update Profile**

**Expected Result:**
- All fields save successfully without errors
- Fields remain populated after page refresh

---

### Test Case 2: GET User Data via REST API

**Test using cURL:**
```bash
curl -X GET "http://yoursite.local/wp-json/wp/v2/users/1" \
  -u "username:password"
```

**Test using Browser (requires authentication):**
```
http://yoursite.local/wp-json/wp/v2/users/1
```

**Expected Response:**
```json
{
  "id": 1,
  "name": "Your Name",
  "url": "",
  "description": "",
  "link": "http://yoursite.local/author/username/",
  "slug": "username",
  "author_profile_image": "https://yoursite.local/wp-content/uploads/...",
  "linkedin_url": "https://linkedin.com/in/yourprofile",
  "twitter_url": "https://twitter.com/yourusername",
  "instagram_url": "https://instagram.com/yourusername",
  "facebook_url": "https://facebook.com/yourprofile",
  "youtube_url": "https://youtube.com/@yourchannel",
  "web_portfolio_url": "https://yourportfolio.com",
  "other_url_1": "",
  "other_url_2": "",
  "other_url_3": "",
  "author_cta_hook": "Follow me for more great content!",
  "author_cta_action_url": "https://yoursite.com/follow",
  ...other user fields...
}
```

**Validation Checklist:**
- [ ] All 12 custom fields are present in the response
- [ ] Field values match what was entered in the admin
- [ ] Empty fields return empty strings (not null)
- [ ] Profile image returns a URL (if set)

---

### Test Case 3: UPDATE User Data via REST API

**Test using cURL:**
```bash
curl -X POST "http://yoursite.local/wp-json/wp/v2/users/1" \
  -u "username:password" \
  -H "Content-Type: application/json" \
  -d '{
    "linkedin_url": "https://linkedin.com/in/updated-profile",
    "author_cta_hook": "Updated CTA message"
  }'
```

**Expected Result:**
- Response returns updated values
- Values persist in the database
- Verify in WordPress admin that fields are updated

**Validation Checklist:**
- [ ] Update request returns status 200
- [ ] Response includes updated field values
- [ ] Changes are visible in WordPress admin (Users → Profile)
- [ ] Other fields remain unchanged

---

### Test Case 4: GET Article with Author Profile

**Prerequisites:**
- Create a test article with content
- Assign yourself as the author
- Ensure your user profile has author fields populated

**Test using cURL:**
```bash
curl -X GET "http://yoursite.local/wp-json/wp/v2/article?per_page=1" \
  -u "username:password"
```

**Expected Response:**
```json
[
  {
    "id": 123,
    "title": {
      "rendered": "My Test Article"
    },
    "author": 1,
    "author_profile": {
      "author_profile_image": "https://yoursite.local/wp-content/uploads/...",
      "linkedin_url": "https://linkedin.com/in/yourprofile",
      "twitter_url": "https://twitter.com/yourusername",
      "instagram_url": "https://instagram.com/yourusername",
      "facebook_url": "https://facebook.com/yourprofile",
      "youtube_url": "https://youtube.com/@yourchannel",
      "web_portfolio_url": "https://yourportfolio.com",
      "other_url_1": "",
      "other_url_2": "",
      "other_url_3": "",
      "author_cta_hook": "Follow me for more great content!",
      "author_cta_action_url": "https://yoursite.com/follow"
    },
    ...other article fields...
  }
]
```

**Validation Checklist:**
- [ ] `author_profile` object is present
- [ ] `author_profile` contains all 12 author fields
- [ ] Field values match the author's profile data
- [ ] Works for both single article and list requests

---

### Test Case 5: GET Project with Author Profile

**Prerequisites:**
- Create a test project with content
- Assign yourself as the author

**Test using cURL:**
```bash
curl -X GET "http://yoursite.local/wp-json/wp/v2/project?per_page=1" \
  -u "username:password"
```

**Expected Response:**
Similar to Test Case 4, but for projects:
```json
[
  {
    "id": 456,
    "title": {
      "rendered": "My Test Project"
    },
    "author": 1,
    "author_profile": {
      ...all 12 author fields...
    },
    ...other project fields...
  }
]
```

**Validation Checklist:**
- [ ] `author_profile` object is present
- [ ] Field values match the author's profile data
- [ ] Works for both single project and list requests

---

### Test Case 6: Multiple Authors

**Test Scenario:**
Test that each author's profile data is correctly associated with their content.

**Steps:**
1. Create a second user account
2. Add different author profile data for the second user
3. Create articles/projects by both users
4. Query articles/projects via REST API

**Expected Result:**
- Each post shows the correct author's profile data
- Author 1's posts show Author 1's profile fields
- Author 2's posts show Author 2's profile fields
- No data mixing between authors

---

### Test Case 7: Empty/Null Field Handling

**Test Scenario:**
Verify behavior when author profile fields are empty.

**Steps:**
1. Create a user with no author profile data
2. Create an article by this user
3. Query the article via REST API

**Expected Result:**
- `author_profile` object is still present
- All fields return empty strings (`""`)
- No errors or missing keys in response

---

### Test Case 8: GraphQL Compatibility

**Verify REST and GraphQL work together:**

**GraphQL Query:**
```graphql
query GetUsers {
  users {
    nodes {
      id
      name
      authorProfileDetails {
        profileImage
        authorSocialLinkedin
        authorSocialTwitter
      }
    }
  }
}
```

**Expected Result:**
- GraphQL queries still work correctly
- Both REST and GraphQL return author profile data
- Data is consistent between both APIs

---

## Common Issues and Solutions

### Issue 1: Fields Not Appearing in REST Response

**Possible Causes:**
- ACF plugin not activated
- Plugin not activated
- `rest_api_init` action not firing

**Solution:**
- Verify plugins are active
- Check for PHP errors in WordPress debug log
- Run automated test suite to diagnose

### Issue 2: 401 Unauthorized Error

**Possible Causes:**
- Authentication credentials not provided
- User doesn't have permission

**Solution:**
- Use Application Passwords for authentication
- Ensure user has appropriate capabilities
- Check WordPress REST API authentication setup

### Issue 3: author_profile Returns Empty Data

**Possible Causes:**
- Author profile fields not populated
- ACF `get_field()` function not working

**Solution:**
- Populate author profile fields in WordPress admin
- Verify ACF is properly installed
- Check that field names match exactly

---

## Performance Testing

### Test Case 9: Query Performance

**Test with multiple posts:**
```bash
curl -X GET "http://yoursite.local/wp-json/wp/v2/article?per_page=100" \
  -u "username:password"
```

**Validation:**
- Response time should be reasonable (< 2 seconds)
- No N+1 query issues
- Author profile data loads efficiently

---

## Security Testing

### Test Case 10: Authentication

**Test without credentials:**
```bash
curl -X GET "http://yoursite.local/wp-json/wp/v2/users/1"
```

**Expected Result:**
- Public user data is visible
- Sensitive fields respect WordPress permissions
- Update operations require authentication

---

## Test Results Template

Use this template to track your test results:

```
Date: ___________
Tester: ___________

| Test Case | Status | Notes |
|-----------|--------|-------|
| TC1: Setup Data | ⬜ Pass / ⬜ Fail | |
| TC2: GET User | ⬜ Pass / ⬜ Fail | |
| TC3: UPDATE User | ⬜ Pass / ⬜ Fail | |
| TC4: GET Article | ⬜ Pass / ⬜ Fail | |
| TC5: GET Project | ⬜ Pass / ⬜ Fail | |
| TC6: Multiple Authors | ⬜ Pass / ⬜ Fail | |
| TC7: Empty Fields | ⬜ Pass / ⬜ Fail | |
| TC8: GraphQL | ⬜ Pass / ⬜ Fail | |
| TC9: Performance | ⬜ Pass / ⬜ Fail | |
| TC10: Security | ⬜ Pass / ⬜ Fail | |

Overall Result: ⬜ Pass / ⬜ Fail
```

---

## Additional Tools

### Using Postman

1. Import REST API endpoints
2. Set up authentication (Basic Auth or Application Passwords)
3. Create a collection with all test cases
4. Run collection to automate testing

### Using WP-CLI

```bash
# Get user data
wp rest get /wp/v2/users/1

# Update user data
wp rest update /wp/v2/users/1 --linkedin_url="https://linkedin.com/in/test"
```

### Browser Extensions

- **REST API Client**: Test endpoints directly in browser
- **JSON Viewer**: Format JSON responses for easier reading

---

## Continuous Testing

For ongoing development:

1. Run automated test suite before deploying
2. Add new test cases for any new features
3. Keep this document updated with findings
4. Document any edge cases discovered

---

**Questions or Issues?**
If you encounter problems during testing, check:
1. WordPress debug log (`wp-content/debug.log`)
2. Browser console for JavaScript errors
3. Network tab for API request/response details
4. PHP error logs
