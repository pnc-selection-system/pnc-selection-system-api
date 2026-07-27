 # Frontend Permission Integration Guide

## Overview
This API provides a permission matrix that frontend applications can use to show/hide sidebar menu items based on user roles.

## API Endpoint

### Get Permission Matrix
```
GET /api/setup/users-roles/permission-matrix
Authorization: Bearer {jwt_token}
```

### Response Format
```json
{
  "success": true,
  "message": "Permission matrix retrieved successfully",
  "data": {
    "rows": [
      {
        "capability": "View users list",
        "name": "users.view",
        "module": "Users",
        "access": {
          "ADM": true,   // Admin has access
          "MGR": false,  // Manager doesn't
          "OFF": false   // Officer doesn't
        }
      },
      {
        "capability": "Configure exam thresholds & subjects",
        "name": "exam.configure",
        "module": "Exam",
        "access": {
          "ADM": true,
          "MGR": true,
          "OFF": false
        }
      }
    ]
  }
}
```

## Implementation Steps

### 1. Fetch Permission Matrix After Login
After successful login, store the JWT token and fetch the permission matrix:

```javascript
// After login
const login = async (email, password) => {
  const response = await fetch('/api/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  
  const data = await response.json();
  if (data.success) {
    localStorage.setItem('token', data.data.access_token);
    localStorage.setItem('user', JSON.stringify(data.data.user));
    
    // Fetch permission matrix
    await fetchPermissionMatrix();
  }
};

// Fetch permission matrix
const fetchPermissionMatrix = async () => {
  const token = localStorage.getItem('token');
  const response = await fetch('/api/setup/users-roles/permission-matrix', {
    headers: { 'Authorization': `Bearer ${token}` }
  });
  
  const data = await response.json();
  if (data.success) {
    localStorage.setItem('permissionMatrix', JSON.stringify(data.data.rows));
  }
};
```

### 2. Create Permission Helper Functions
```javascript
// Check if user has specific permission
const hasPermission = (permissionName) => {
  const matrix = JSON.parse(localStorage.getItem('permissionMatrix') || '[]');
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  
  // Map role names to matrix keys
  const roleMap = {
    'Admin': 'ADM',
    'Manager': 'MGR',
    'Officer': 'OFF'
  };
  
  const userRoleKey = roleMap[user.role] || 'OFF';
  
  const permission = matrix.find(p => p.name === permissionName);
  return permission && permission.access[userRoleKey];
};

// Check if user is Admin
const isAdmin = () => {
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  return user.role === 'Admin';
};

// Check if user is Manager
const isManager = () => {
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  return user.role === 'Manager';
};

// Check if user is Officer
const isOfficer = () => {
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  return user.role === 'Officer';
};
```

### 3. Implement Sidebar with Permissions
```javascript
// Define menu items with required permissions
const menuItems = [
  {
    id: 'dashboard',
    label: 'Dashboard',
    icon: 'home',
    path: '/dashboard',
    permission: null // Visible to all
  },
  {
    id: 'user-role',
    label: 'User Role Management',
    icon: 'users',
    path: '/setup/users-roles',
    permission: 'users.view' // Only visible if user has this permission
  },
  {
    id: 'campaigns',
    label: 'Campaigns',
    icon: 'calendar',
    path: '/campaigns',
    permission: 'campaigns.view'
  },
  {
    id: 'candidates',
    label: 'Candidates',
    icon: 'user-check',
    path: '/candidates',
    permission: 'candidates.view'
  },
  {
    id: 'exams',
    label: 'Exams',
    icon: 'file-text',
    path: '/exams',
    permission: 'exam.view'
  },
  {
    id: 'voting',
    label: 'Voting',
    icon: 'check-square',
    path: '/voting',
    permission: 'voting.view'
  }
];

// Filter menu items based on permissions
const getVisibleMenuItems = () => {
  return menuItems.filter(item => {
    // If no permission required, show to all
    if (!item.permission) return true;
    
    // Otherwise check if user has the permission
    return hasPermission(item.permission);
  });
};

// Render sidebar
const Sidebar = () => {
  const visibleItems = getVisibleMenuItems();
  
  return (
    <aside>
      <nav>
        {visibleItems.map(item => (
          <Link key={item.id} to={item.path}>
            <Icon name={item.icon} />
            <span>{item.label}</span>
          </Link>
        ))}
      </nav>
    </aside>
  );
};
```

### 4. React Example with Conditional Rendering
```jsx
import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';

const Sidebar = () => {
  const [menuItems, setMenuItems] = useState([]);
  const [user, setUser] = useState(null);
  const [permissionMatrix, setPermissionMatrix] = useState([]);

  useEffect(() => {
    // Load user and permissions from localStorage
    const storedUser = JSON.parse(localStorage.getItem('user') || '{}');
    const storedMatrix = JSON.parse(localStorage.getItem('permissionMatrix') || '[]');
    
    setUser(storedUser);
    setPermissionMatrix(storedMatrix);
    
    // Filter menu items
    const filteredItems = menuItemsDef.filter(item => {
      if (!item.permission) return true;
      return hasPermission(item.permission, storedUser, storedMatrix);
    });
    
    setMenuItems(filteredItems);
  }, []);

  return (
    <aside className="sidebar">
      {menuItems.map(item => (
        <Link key={item.id} to={item.path} className="menu-item">
          {item.icon && <span className="icon">{item.icon}</span>}
          <span className="label">{item.label}</span>
        </Link>
      ))}
    </aside>
  );
};

export default Sidebar;
```

### 5. Vue Example
```vue
<template>
  <aside class="sidebar">
    <router-link
      v-for="item in visibleMenuItems"
      :key="item.id"
      :to="item.path"
      class="menu-item"
    >
      <span v-if="item.icon" class="icon">{{ item.icon }}</span>
      <span class="label">{{ item.label }}</span>
    </router-link>
  </aside>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useStore } from 'vuex';

const store = useStore();

const menuItems = [
  { id: 'dashboard', label: 'Dashboard', path: '/dashboard', permission: null },
  { id: 'user-role', label: 'User Role Management', path: '/setup/users-roles', permission: 'users.view' },
  { id: 'candidates', label: 'Candidates', path: '/candidates', permission: 'candidates.view' },
  { id: 'exams', label: 'Exams', path: '/exams', permission: 'exam.view' }
];

const visibleMenuItems = computed(() => {
  const user = store.state.user;
  const matrix = store.state.permissionMatrix;
  
  return menuItems.filter(item => {
    if (!item.permission) return true;
    return hasPermission(item.permission, user, matrix);
  });
});

const hasPermission = (permissionName, user, matrix) => {
  const roleMap = {
    'Admin': 'ADM',
    'Manager': 'MGR',
    'Officer': 'OFF'
  };
  
  const userRoleKey = roleMap[user.role] || 'OFF';
  const permission = matrix.find(p => p.name === permissionName);
  
  return permission && permission.access[userRoleKey];
};

onMounted(async () => {
  // Fetch permission matrix if not already loaded
  if (!store.state.permissionMatrix.length) {
    await store.dispatch('fetchPermissionMatrix');
  }
});
</script>
```

## Permission Matrix Reference

Based on the permission matrix image, here are the permissions for each role:

### Admin (ADM) - Full Access
- ✅ Manage users (users.view, users.create, users.edit, users.deactivate, roles.manage)
- ✅ Configure exam (exam.configure)
- ✅ Edit candidate (candidates.view, candidates.create, candidates.edit, candidates.import)
- ✅ Publish results (exam.results)
- ❌ Cast vote (voting.cast) - NOT allowed

### Manager (MGR) - Limited Access
- ❌ Manage users
- ✅ Configure exam (exam.configure)
- ✅ Edit candidate (candidates.view, candidates.create, candidates.edit, candidates.import)
- ✅ Publish results (exam.results)
- ❌ Cast vote (voting.cast)

### Officer (OFF) - Minimal Access
- ❌ Manage users
- ❌ Configure exam
- ✅ Edit candidate (candidates.view, candidates.create, candidates.edit, candidates.import)
- ❌ Publish results
- ❌ Cast vote

## Testing the Integration

### 1. Test with Admin User
```bash
# Login
POST /api/auth/login
{
  "email": "admin@gmail.com",
  "password": "admin123"
}

# Get permission matrix
GET /api/setup/users-roles/permission-matrix
Authorization: Bearer {token}
```

Expected: Admin should have all permissions including `users.view`, so the "UserRole" menu should be visible.

### 2. Test with Manager User
```bash
# Login
POST /api/auth/login
{
  "email": "manager@gmail.com",
  "password": "manager123"
}
```

Expected: Manager should NOT have `users.view` permission, so the "UserRole" menu should be hidden.

### 3. Test with Officer User
```bash
# Login
POST /api/auth/login
{
  "email": "officer@gmail.com",
  "password": "officer123"
}
```

Expected: Officer should NOT have `users.view` permission, so the "UserRole" menu should be hidden.

## Troubleshooting

### Issue: UserRole menu not showing for Admin
**Solution:** 
1. Verify the user's role is "Admin" (not "ADM")
2. Check that the permission matrix API returns `"ADM": true` for `users.view`
3. Ensure the frontend is mapping "Admin" role to "ADM" key correctly
4. Clear localStorage and re-login

### Issue: Menu shows for all roles
**Solution:**
1. Make sure you're checking permissions before rendering menu items
2. Verify the permission matrix is being fetched and stored
3. Check that `hasPermission()` function is working correctly

### Issue: Permission matrix not updating after role changes
**Solution:**
The permission matrix is cached for 1 hour. To force refresh:
```javascript
// Clear the cache by re-fetching
await fetchPermissionMatrix();
```

Or in the backend, the cache is automatically cleared when permissions are modified via the admin panel.

## Available Permissions

The system has 35 permissions across 9 modules:

### Users & Roles
- users.view - View users list
- users.create - Create new users
- users.edit - Edit existing users
- users.deactivate - Deactivate/activate users
- roles.manage - Manage roles & permissions

### Campaign
- campaigns.view - View campaigns
- campaigns.create - Create campaigns
- campaigns.edit - Edit campaigns
- campaigns.delete - Delete campaigns

### Info Sessions
- sessions.view - View info sessions
- sessions.create - Create info sessions
- sessions.edit - Edit info sessions
- sessions.delete - Delete info sessions

### NGO Partners
- ngos.view - View NGO partners
- ngos.create - Create NGO partners
- ngos.edit - Edit NGO partners
- ngos.delete - Delete NGO partners

### Candidates
- candidates.view - View candidates
- candidates.create - Create candidates
- candidates.edit - Edit candidates
- candidates.delete - Delete candidates
- candidates.import - Import candidates from file

### Exam
- exam.view - View exams/subjects
- exam.configure - Configure exam thresholds & subjects
- exam.import - Import exam results
- exam.results - View/publish exam results

### Assessment
- assessment.view - View assessments & forms
- assessment.manage - Manage assessment forms & responses

### Home Investigation
- homeinv.view - View home investigations
- homeinv.manage - Manage home investigations

### Voting
- voting.view - View voting rounds & votes
- voting.cast - Cast votes
- voting.manage - Manage voting rounds

### Reports
- reports.view - View reports & analytics
- reports.export - Export reports

## Notes

- The permission matrix is cached for 1 hour for performance
- Cache is automatically cleared when admin modifies permissions
- All API routes (except login) require JWT authentication
- Permission checks are done both at API level (middleware) and frontend level (UI hiding)