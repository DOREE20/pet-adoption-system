# Refactor & Merge Plan (Approved with Corrections)
Keep catalogue.html separate. Prefix emp_ APIs in api.php. UI unchanged.

## Steps:
1. [x] Update app.js: Add routing utils, change OPS_BASE='api.php'
2. [x] Create auth.html: Merge login.html + register.html with ?page= routing
3. [x] Update navbars in home.html, catalogue.html, dashboard.html → auth.html?page=*
 - [x] Remove pest.html + links
4. [x] Create services.html: Merge shopping.html + playtime.html with ?page= routing
5. [x] Update navbars → services.html?page=shopping/playtime (remove pest)
6. [ ] Update dashboard.html: Merge feedback.html as ?section=feedback tab
5. [ ] Update navbars → services.html?page=shopping/playtime (remove pest)
6. [ ] Update dashboard.html: Merge feedback.html as ?section=feedback tab
7. [ ] Update employee.html: Merge employee_panel.html content (role tabs)
8. [ ] Create auth_handler.php: Merge sessions.php + cookies.php
9. [ ] Merge operations.php into api.php (prefix emp_*)
10. [ ] Delete originals: login.html, register.html, shopping.html, playtime.html, feedback.html, employee_panel.html, sessions.php, cookies.php, operations.php, pest.html
11. [ ] Test all routes/forms/endpoints
12. [ ] Mark complete & cleanup TODO.md

