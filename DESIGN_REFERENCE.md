# 🎨 Visual Design Reference - EduScout v2.0

## Color Palette

### Primary Colors
```
Primary Gradient: #667eea → #764ba2 (Purple/Blue)
  Used for: Buttons, links, headers, primary actions

Light Primary: #f0f7ff (Light blue background)
  Used for: Info boxes, backgrounds
```

### Status Colors
```
Success:  #28a745 (Green)  - Approve, bookmark, confirm actions
Danger:   #dc3545 (Red)    - Delete, reject, warnings
Warning:  #ffc107 (Amber)  - Info, caution messages
Info:     #667eea (Blue)   - Informational messages
```

### Neutral Colors
```
Text Dark:     #333 (Almost black)
Text Light:    #666 (Gray)
Text Muted:    #999 (Lighter gray)
Borders:       #e0e0e0 (Light gray)
Background:    #f9f9f9 (Off-white)
White:         #ffffff
```

---

## Typography

### Font Family
```
Primary: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif
```

### Font Sizes (Desktop)
```
h1: 2.0rem (32px) - Page titles
h2: 1.5rem (24px) - Section headers
h3: 1.1rem (18px) - Card titles
p:  1.0rem (16px) - Body text
small: 0.85rem (14px) - Captions
```

### Font Sizes (Mobile)
```
h1: 1.2rem (19px)
h2: 1.3rem (21px)
h3: 1.0rem (16px)
p:  0.95rem (15px)
```

### Font Weights
```
Normal: 400
Medium: 500 (labels, nav)
Bold:   600 (headings, strong)
Heavy:  700 (main headings)
```

---

## Spacing System

### Desktop Padding/Margins
```
2rem (32px) - Page sections, large containers
1.5rem (24px) - Card padding, section spacing
1rem (16px) - Component padding, general spacing
0.5rem (8px) - Small gaps between elements
```

### Mobile Padding/Margins
```
1rem (16px) - Page sections
0.75rem (12px) - Card padding
0.5rem (8px) - Component padding
0.25rem (4px) - Small gaps
```

### Gaps in Grids
```
Desktop: 2rem (32px)
Tablet:  1.5rem (24px)
Mobile:  1rem (16px)
```

---

## Responsive Breakpoints

### Breakpoint Values
```
Small Mobile:       < 480px
Mobile:             480px - 768px
Tablet:             768px - 1024px
Desktop:            > 1024px
```

### Grid Changes
```
Desktop:
  - Sidebar + Content (250px + 1fr)
  - Course cards: repeat(auto-fill, minmax(280px, 1fr))
  - Course Meta: repeat(auto-fit, minmax(200px, 1fr))

Mobile:
  - Single column (100%)
  - Course cards: 1fr (full width)
  - Course Meta: 1fr (stacked)
```

### Navigation Changes
```
Desktop:
  - Horizontal navbar
  - No hamburger menu
  - Visible all items

Mobile:
  - Hidden nav (display: none by default)
  - Hamburger menu visible (≡)
  - Vertical menu items when open
  - Full width dropdown
```

---

## Component Styling

### Buttons
```
Primary Button:
  Background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)
  Color: white
  Padding: 0.75rem 1.5rem
  Border-radius: 6px
  Font-weight: 600
  
  Hover: 
    Transform: translateY(-2px)
    Box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3)
```

### Form Inputs
```
Border: 2px solid #e0e0e0
Padding: 0.75rem
Border-radius: 6px
Font-size: 1rem

Focus:
  Border-color: #667eea
  Box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1)
  Outline: none
```

### Cards
```
Background: white
Box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1)
Border-radius: 8px - 12px
Padding: 1.5rem - 2rem

Hover:
  Box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15)
  Transform: translateY(-5px)
```

### Badges
```
Background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)
Color: white
Padding: 0.5rem 1rem
Border-radius: 20px
Font-weight: 600
Font-size: 0.9rem
```

---

## Course Card Layout

### Desktop (280px width)
```
┌─────────────┐
│   IMAGE     │  (200px height, 16:9 ratio)
│ (16:9)      │
├─────────────┤
│  Title      │
│  (2 lines)  │
├─────────────┤
│ Duration    │
│ Provider    │
│ Bookmark btn│
└─────────────┘
```

### Mobile (full width)
```
┌──────────────────────────┐
│      IMAGE               │
│      (16:9)              │
├──────────────────────────┤
│ Title                    │
│ (2 lines, 1rem)          │
├──────────────────────────┤
│ Duration                 │
│ Provider                 │
│ [Bookmark Button]        │
└──────────────────────────┘
```

---

## Navigation Styles

### Desktop Header
```
┌────────────────────────────────────────┐
│ 🧠 EduScout │ Home Bookmarks Profile   │
│             │ Submit Review Manage     │
└────────────────────────────────────────┘
```

### Mobile Header
```
┌─────────────────────────────┐
│ ≡  🧠 EduScout              │
├─────────────────────────────┤
│ Home                        │
│ My Bookmarks                │
│ Profile (username)          │
│ Submit Course               │
│ Manage Categories           │
│ Logout                      │
└─────────────────────────────┘
```

---

## Sidebar & Category Styles

### Desktop Sidebar
```
┌─────────────┐
│ 📚 Fields   │
├─────────────┤
│ 💻 IT       │
│ 🏥 Medical  │
│ ⚙️ Eng.     │
│ 🎨 Arts     │
└─────────────┘

Width: 250px
Background: white
Box-shadow: 0 4px 15px rgba(0,0,0,0.1)

Active Link:
  Background: gradient (#667eea → #764ba2)
  Color: white
  Border-radius: 6px
```

### Mobile Sidebar
```
Only visible in hamburger menu
Full width in dropdown
Stacked vertically
Border-bottom on each item
```

---

## Empty State Design

```
┌─────────────────────────────────────┐
│                                     │
│            📚                       │
│     (Large emoji - 4rem)            │
│                                     │
│   No Bookmarks Yet                  │
│   (h2 - color: #999)                │
│                                     │
│  You haven't bookmarked             │
│  any courses yet.                   │
│  (p - color: #999)                  │
│                                     │
│  [← Explore Courses]                │
│  (Link - color: #667eea)            │
│                                     │
└─────────────────────────────────────┘

Background: white
Box-shadow: 0 4px 15px rgba(0,0,0,0.1)
Padding: 4rem 2rem
Text-align: center
```

---

## Message Styles

### Success Message
```
Background: #d4edda (Light green)
Border: 1px solid #c3e6cb
Color: #155724 (Dark green)
Padding: 1rem
Border-radius: 6px
Icon: ✅
```

### Error Message
```
Background: #f8d7da (Light red)
Border: 1px solid #f5c6cb
Color: #721c24 (Dark red)
Padding: 1rem
Border-radius: 6px
Icon: ❌
```

### Info Message
```
Background: #f0f7ff (Light blue)
Border: 1px solid #667eea
Color: #555 (Gray)
Padding: 1rem
Border-radius: 6px
Icon: ℹ️
```

---

## Emoji Icons Used

### Navigation & Categories
```
🧠 - EduScout logo
📚 - Books, learning, general
💻 - IT, technology, computers
🏥 - Medical, healthcare
⚙️ - Engineering, mechanics
🎨 - Arts, design, creativity
📊 - Data, analytics
🔬 - Science, research
🎓 - Education, graduation
🌐 - Web, internet, global
🤖 - AI, automation, tech
📱 - Mobile, apps
🎬 - Video, movies, courses
```

### Actions
```
✏️ - Edit
🗑️ - Delete
⭐ - Bookmarked
☆ - Unbookmarked
🚀 - Submit, launch
👤 - Profile, user
📺 - Video, provider
⏱️ - Duration, time
📂 - Category, folder
👨‍💼 - Admin, administrator
```

---

## Hover & Active States

### Links
```
Normal: color: #667eea; text-decoration: none;
Hover: text-decoration: underline;
Active: color: #667eea; border-bottom: 2px solid #667eea;
```

### Buttons
```
Normal:
  background: gradient
  color: white
  transform: none

Hover:
  transform: translateY(-2px)
  box-shadow: enhanced
  opacity: 1

Active:
  transform: translateY(0)
  
Disabled:
  opacity: 0.6
  cursor: not-allowed
```

### Cards
```
Normal:
  box-shadow: 0 4px 15px rgba(0,0,0,0.1)
  transform: none

Hover:
  box-shadow: 0 8px 25px rgba(0,0,0,0.15)
  transform: translateY(-5px)
```

---

## Transitions & Animations

### All Transitions
```
Duration: 0.3s
Timing: ease
Properties: all (for most elements)

Exceptions:
  Shadows: box-shadow only
  Position: transform only
  Color: color, background-color only
```

### Specific Animations
```
Link hover: border-bottom slide
Button hover: lift up + shadow
Card hover: shadow expand + lift
Menu toggle: opacity fade
```

---

## Accessibility Features

### Color Contrast
```
✅ All text meets WCAG AA standard (4.5:1 minimum)
✅ Links are underlined or have distinct color
✅ Buttons have sufficient size (44px minimum touch)
```

### Responsive Text
```
✅ Font sizes scale with viewport
✅ Line-height maintains readability (1.6)
✅ Letter-spacing improved for mobile
```

### Touch Targets
```
✅ Buttons: minimum 44px × 44px
✅ Links: minimum 44px × 44px
✅ Form inputs: 48px minimum height
✅ Gap between clickables: 8px minimum
```

---

## Browser Support

```
✅ Chrome 90+
✅ Firefox 88+
✅ Safari 14+
✅ Edge 90+
✅ Mobile Safari (iOS 14+)
✅ Chrome Mobile (Android)
```

### Fallbacks
```
- Gradients: solid color fallback
- Shadows: supported by all
- Grid: supported by all
- Flexbox: supported by all
- CSS Custom Properties: used with fallbacks
```

---

This design system ensures consistency across all pages while maintaining responsiveness and accessibility! 🎨
