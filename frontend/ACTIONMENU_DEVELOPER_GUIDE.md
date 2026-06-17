# ActionMenu Component - Developer Guide

## Quick Start

The ActionMenu component provides a modern three-dot menu for row-level CRUD operations throughout RDRIMS.

## Basic Usage

### 1. Import the Component
```vue
<script setup>
import ActionMenu from '@/components/ActionMenu.vue'
</script>
```

### 2. Add to Template
```vue
<template>
  <div v-for="item in items" :key="item.id" class="flex justify-between">
    <div>{{ item.name }}</div>
    <ActionMenu :actions="getActions(item)" />
  </div>
</template>
```

### 3. Define Actions
```vue
<script setup>
function getActions(item) {
  return [
    { 
      key: 'view', 
      label: 'View Details', 
      handler: () => viewItem(item) 
    },
    { 
      key: 'edit', 
      label: 'Edit', 
      handler: () => editItem(item) 
    },
    { separator: true },
    { 
      key: 'delete', 
      label: 'Delete', 
      handler: () => deleteItem(item) 
    }
  ]
}
</script>
```

## Props

### `actions` (required)
Array of action objects with the following properties:

| Property | Type | Required | Description |
|----------|------|----------|-------------|
| `key` | String | No | Action identifier (auto-selects icon and hover color) |
| `label` | String | Yes | Display text |
| `handler` | Function | Yes | Click handler function |
| `show` | Boolean | No | Conditional visibility (default: `true`) |
| `disabled` | Boolean | No | Disabled state (default: `false`) |
| `separator` | Boolean | No | Show separator line instead of action |
| `icon` | String | No | Custom icon key (overrides auto-detection) |
| `iconComponent` | Component | No | Custom Vue component for icon |

### `size` (optional)
- `'sm'` - Small button (32px) - **Default**
- `'md'` - Medium button (40px)

### `align` (optional)
- `'right'` - Align dropdown to right - **Default**
- `'left'` - Align dropdown to left

## Action Keys & Auto-Styling

The component automatically assigns icons and hover colors based on the `key` property:

| Key Pattern | Icon | Hover Color |
|-------------|------|-------------|
| `view` | Eye | Blue |
| `edit` | Pencil | Blue |
| `delete`, `remove` | Trash | Red |
| `approve`, `accept` | Check | Green |
| `reject` | X Circle | Orange |
| `assign`, `user` | User Plus | Default |
| `download` | Download | Indigo |
| `permissions`, `shield` | Shield | Default |
| `versions`, `history` | Clock | Default |
| `link` | External Link | Default |

## Examples

### Basic Actions
```vue
<ActionMenu :actions="[
  { key: 'view', label: 'View', handler: () => view() },
  { key: 'edit', label: 'Edit', handler: () => edit() },
  { key: 'delete', label: 'Delete', handler: () => remove() }
]" />
```

### With Separator
```vue
<ActionMenu :actions="[
  { key: 'view', label: 'View Details', handler: () => view() },
  { key: 'edit', label: 'Edit Item', handler: () => edit() },
  { separator: true },
  { key: 'delete', label: 'Delete Item', handler: () => remove() }
]" />
```

### Conditional Actions
```vue
<ActionMenu :actions="[
  { 
    key: 'approve', 
    label: 'Approve', 
    handler: () => approve(), 
    show: !item.approved 
  },
  { 
    key: 'reject', 
    label: 'Reject', 
    handler: () => reject(), 
    show: !item.rejected 
  },
  { 
    key: 'download', 
    label: 'Download', 
    handler: () => download(), 
    show: item.has_file 
  }
]" />
```

### With Permissions
```vue
<script setup>
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

function getActions(item) {
  return [
    { key: 'view', label: 'View', handler: () => view(item) },
    { 
      key: 'edit', 
      label: 'Edit', 
      handler: () => edit(item),
      show: auth.hasRole('admin', 'editor')
    },
    { separator: true, show: auth.hasRole('admin') },
    { 
      key: 'delete', 
      label: 'Delete', 
      handler: () => remove(item),
      show: auth.hasRole('admin')
    }
  ]
}
</script>
```

### Small Size & Left Align
```vue
<ActionMenu 
  :actions="actions" 
  size="sm" 
  align="left" 
/>
```

### In Table Rows
```vue
<table>
  <tbody>
    <tr v-for="item in items" :key="item.id">
      <td>{{ item.name }}</td>
      <td>{{ item.status }}</td>
      <td class="text-right">
        <ActionMenu :actions="[
          { key: 'view', label: 'View', handler: () => view(item) },
          { key: 'edit', label: 'Edit', handler: () => edit(item) },
          { separator: true },
          { key: 'delete', label: 'Delete', handler: () => remove(item) }
        ]" />
      </td>
    </tr>
  </tbody>
</table>
```

### In Card Layouts
```vue
<div v-for="item in items" :key="item.id" class="card p-4">
  <div class="flex items-start justify-between">
    <div class="flex-1">
      <h3>{{ item.title }}</h3>
      <p>{{ item.description }}</p>
    </div>
    <ActionMenu :actions="[
      { key: 'edit', label: 'Edit', handler: () => edit(item) },
      { key: 'delete', label: 'Delete', handler: () => remove(item) }
    ]" />
  </div>
</div>
```

### With Router Navigation
```vue
<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

function getActions(item) {
  return [
    { 
      key: 'view', 
      label: 'View Details', 
      handler: () => router.push(`/app/items/${item.id}`) 
    },
    { 
      key: 'edit', 
      label: 'Edit', 
      handler: () => router.push(`/app/items/${item.id}/edit`) 
    }
  ]
}
</script>
```

### Stop Event Propagation (in clickable containers)
```vue
<div 
  v-for="item in items" 
  :key="item.id" 
  @click="viewItem(item)"
  class="cursor-pointer"
>
  <h3>{{ item.title }}</h3>
  <ActionMenu 
    :actions="actions" 
    @click.stop 
  />
</div>
```

## Best Practices

### ✅ DO
- Use ActionMenu for repetitive row-level CRUD operations
- Use conditional `show` property for permission-based actions
- Use separators to group related actions
- Stop event propagation when inside clickable containers
- Keep action labels concise and clear
- Use the automatic key-based styling when possible

### ❌ DON'T
- Don't use ActionMenu for primary workflow actions (Submit, Approve main flow)
- Don't use for single-action items (just use a button)
- Don't nest ActionMenus
- Don't override the automatic styling without good reason
- Don't put too many actions in one menu (max 6-8 recommended)

## Styling Customization

The ActionMenu uses TailwindCSS classes and can be customized through the component props. For global styling changes, edit the component file directly.

## Accessibility

The component includes:
- Proper ARIA labels
- Keyboard navigation support
- Focus states
- Screen reader announcements
- Escape key to close

No additional accessibility work is needed when using the component.

## Browser Support

Works in all modern browsers:
- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers

## Performance

The component is lightweight and performant:
- Small bundle size
- Efficient event listeners
- Automatic cleanup on unmount
- No unnecessary re-renders

## Troubleshooting

### Menu doesn't close when clicking outside
Ensure you're not stopping propagation on parent elements.

### Actions not showing
Check the `show` property - it might be set to `false`.

### Icons not appearing
Verify the `key` property matches one of the supported patterns, or provide a custom `icon`.

### Menu appears under other elements
Ensure parent containers don't have `overflow: hidden` or adjust z-index.

---

**Component Location:** `frontend/src/components/ActionMenu.vue`  
**Last Updated:** June 16, 2026
