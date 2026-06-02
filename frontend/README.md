# 🎨 Barangay Information System — Vue 3 Frontend Documentation

Welcome to the frontend documentation for the **Web-Based Barangay Information System with Automated Workflow Management**. This client application is built with **Vue 3**, **Vite**, **Pinia**, **Vue Router**, and styled with **Bootstrap 5 + Bootstrap Icons**.

---

## 🗺️ Project Structure & Directory Map

Understanding where files are located is key to debugging and expanding the application:

```
frontend/
├── .env                     # Contains client-side environment variables (e.g. VITE_API_URL)
├── vite.config.js           # Vite server settings & reverse-proxy configuration
├── index.html               # Main HTML shell (loads Google Fonts and sets site title)
└── src/
    ├── main.js              # Entrypoint: registers Pinia, Router, Bootstrap JS, and Global CSS
    ├── style.css            # Custom CSS: theme colors, layout variables, glassmorphism card designs
    ├── services/
    │   └── api.js           # Axios instance: handles baseURL, authorization headers, and auto-logout on 401s
    ├── stores/
    │   └── auth.js          # Pinia Authentication Store: manages tokens, active user role state, and logout
    ├── router/
    │   └── index.js         # Vue Router: holds all view paths and role-based Navigation Guards (admin vs. staff)
    ├── components/          # Reusable shared components (e.g., PhotoUpload.vue, HouseholdGroup.vue)
    └── views/               # Complete Page Views grouped by module:
        ├── admin/           # Admin-exclusive management pages (e.g., UserManagementView.vue)
        ├── announcements/   # Announcements creation, feed, and targeted audience settings
        ├── blotters/        # Blotter logging, party associations, status updates, and reports
        ├── dashboard/       # Dashboards with charts (reports, revenue, case count analytics)
        ├── documents/       # Request processing, approval flows, receipt logging, and PDF previews
        ├── residents/       # Registry listings, registration forms, and profile cards
        └── LoginView.vue    # Login page with validation and auth triggers
```

---

## 🛠️ Common Debugging Scenarios

If you encounter issues during your local development or presentation, refer to these steps:

### Scenario 1: "Unable to connect to the server" or Network Error on Login
* **Problem**: The frontend is running, but API requests are failing.
* **Troubleshooting Steps**:
  1. Ensure the Laravel backend is running. Open a terminal in `backend/` and run `php artisan serve`.
  2. Verify Vite proxy target. Open `vite.config.js` and check line 10. The `target` must match the URL where Laravel is running:
     ```javascript
     proxy: {
       '/api': {
         target: 'http://127.0.0.1:8000', // Or http://localhost:8000
         changeOrigin: true,
       }
     }
     ```
  3. Ensure `frontend/.env` contains exactly `VITE_API_URL=/api/v1` so that requests route through the proxy.

### Scenario 2: Elements are Cluttered or Sidebar Layout is Broken
* **Problem**: Columns do not line up, text-alignment is broken, or icons do not display.
* **Troubleshooting Steps**:
  1. Open `src/main.js` and ensure Bootstrap JS is imported:
     ```javascript
     import 'bootstrap/dist/js/bootstrap.bundle.min.js';
     ```
  2. Open `src/style.css`. Make sure the global CSS rules (like `#app`, `body`, `.page-content`) are not being overridden by old boilerplate CSS. Our premium custom theme properties (such as HSL color tokens for borders, cards, and backgrounds) are declared here.

### Scenario 3: Getting Kicked Out to the Login Page on Refresh
* **Problem**: The system logs you out automatically or displays a blank screen.
* **Troubleshooting Steps**:
  1. Check the Axios interceptor in `src/services/api.js`. If the backend returns a `401 Unauthorized` (e.g. if the token expired or the database was refreshed), the interceptor automatically calls `authStore.clearAuth()` and redirects you to the login screen.
  2. If you just ran `php artisan migrate:fresh --seed` to reset the database, old tokens stored in your browser's local storage will become invalid. Clear your browser cache/cookies or local storage to resolve.

### Scenario 4: Exposing New Columns in the Residents Table
* **Problem**: You want to add or modify columns on the registry table.
* **Troubleshooting Steps**:
  1. Open `src/views/residents/ResidentListView.vue`. Add a column header in the table template (`<thead>`).
  2. In the `<tbody>` row loop (`v-for="resident in residents"`), display your new property (e.g., `{{ resident.your_new_field }}`).
  3. Ensure that your Laravel backend's `ResidentResource.php` is exposing this field in its JSON return array, otherwise it will be undefined in the frontend.

---

## 💡 Recommendations for Future Upgrades

To make this project stand out further, consider implementing these suggestions:

1. **Client-Side Form Validation (VeeValidate)**:
   * *Why*: Currently, forms rely on basic HTML5 validation. Integrating a library like **VeeValidate** (with Yup schemas) allows for immediate, inline error messages (e.g., checking if an email is already formatted correctly, or if contact number starts with `09` and is exactly 11 digits) before submission.
2. **Real-time Client Notifications (Laravel Echo / Websockets)**:
   * *Why*: Rather than refreshing the page or relying on the telemetry dashboard, implement Laravel Echo and Pusher. When n8n triggers an automated dispatch or when a document is approved, the admin/staff will receive a sleek, animated toast alert in the top-right corner of their screen in real-time.
3. **Loading Placeholder Skeletons**:
   * *Why*: While the spinner is functional, modern web apps use **Skeleton Cards** (gray pulsing boxes matching the content layout) while fetching asynchronous data. Replacing spinner tags in views like `ResidentListView.vue` with skeletons will make loading transitions feel premium.
4. **Client-Side Image Compression**:
   * *Why*: Residents upload profile photos that might be several megabytes in size, slowing down the server request. Adding a client-side library (like `browser-image-compression`) in [ResidentFormView.vue](file:///d:/VSC_Projects/Web-Based%20Barangay%20Information%20System%20with%20Automated%20Workflow%20Management/frontend/src/views/residents/ResidentFormView.vue) to scale down images before dispatching them to `/residents/{id}/photo` will improve application performance.
