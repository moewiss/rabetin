# 🚨 IMPORTANT: Run Database Migration

## The Issue

You're seeing:
- ❌ Templates not appearing in wizard
- ❌ "Go to Dashboard" button might not be working properly

## The Fix

You need to run the database migration to create the templates table and insert the 10 templates.

---

## 🚀 Quick Fix (Windows PowerShell)

### Step 1: Open PowerShell in your project directory

```powershell
cd D:\work\rabetin\rabetin
```

### Step 2: Run the migration

```powershell
mysql -u rabetin -pMoody-006M rabetin < design_system_migration.sql
```

**Or if that doesn't work, try:**

```powershell
Get-Content design_system_migration.sql | mysql -u rabetin -pMoody-006M rabetin
```

---

## ✅ Verify It Worked

### Option 1: Visit test page

Open browser and go to:
```
http://localhost/test_templates.php
```

You should see:
- ✅ Found 10 templates
- List of all templates

### Option 2: MySQL command

```bash
mysql -u rabetin -pMoody-006M rabetin -e "SELECT COUNT(*) as template_count FROM design_templates;"
```

Should show: **10**

---

## 📱 Test the Wizard

1. **Logout** or open **Incognito window**
2. Go to: `http://localhost/signup.php`
3. Create new test account
4. Go through wizard steps:
   - Step 1: Enter name
   - Step 2: Choose theme
   - **Step 3: Should now show 10 beautiful template cards!** ✨
   - Step 4: Add link (or skip)
   - Step 5: Click "Go to Dashboard" → should work!

---

## 🔧 Alternative: Manual SQL Import

If the command line isn't working:

### Using phpMyAdmin:
1. Open **phpMyAdmin** in browser
2. Select database: **rabetin**
3. Click **Import** tab
4. Click **Choose File**
5. Select: `design_system_migration.sql`
6. Click **Go**
7. Should see success message

### Using MySQL Workbench:
1. Open **MySQL Workbench**
2. Connect to your database
3. Click **File → Run SQL Script**
4. Select: `design_system_migration.sql`
5. Click **Run**

---

## 🎯 What This Creates

### Tables:
- Adds columns to `profiles` table for design settings
- Creates `design_templates` table

### Data:
Inserts 10 beautiful templates:
1. ✨ Creator (Blogger)
2. 💻 TechPro (Developer)
3. ⚙️ Engineer (Industrial)
4. 🎨 Artist (Creative)
5. 💼 Business (Corporate)
6. 🏋️ Fitness (Health)
7. 🎵 Musician (Music)
8. 👗 Fashion (Beauty)
9. 🍕 Foodie (Restaurant)
10. 📚 Educator (Teacher)

---

## 🆘 Still Not Working?

### Check MySQL is running:
```bash
mysql -u rabetin -pMoody-006M -e "SELECT 1;"
```

### Check database exists:
```bash
mysql -u rabetin -pMoody-006M -e "SHOW DATABASES LIKE 'rabetin';"
```

### Check you're in the right directory:
```bash
pwd  # or "cd" on Windows
ls design_system_migration.sql  # or "dir" on Windows
```

### Check file exists:
The file `design_system_migration.sql` should be in your project root directory:
```
D:\work\rabetin\rabetin\design_system_migration.sql
```

---

## 💡 After Migration Runs Successfully

1. **Refresh wizard page** - templates should appear!
2. **Test selecting templates** - click any card
3. **Complete wizard** - button should work
4. **Check dashboard Design tab** - templates there too!

---

## 🎉 Success Looks Like This

### Wizard Step 3:
```
┌─────────────────────────────────────┐
│  ✨ Choose your style               │
│                                      │
│  ╔══════╗ ╔══════╗ ╔══════╗ ╔══════╗│
│  ║ 💜💜 ║ ║ 💙💙 ║ ║ 🧡🧡 ║ ║ 💖💖 ║│
│  ║ ▬▬▬  ║ ║ ▬▬▬  ║ ║ ▬▬▬  ║ ║ ▬▬▬  ║│
│  ╚══════╝ ╚══════╝ ╚══════╝ ╚══════╝│
│  Creator  TechPro  Engineer  Artist │
│     ✓                                │
│                                      │
│  [← Back]              [Next →]     │
└─────────────────────────────────────┘
```

### Dashboard Design Tab:
Same beautiful template cards with:
- Animated gradients ✨
- Hover effects 💫
- Selection checkmarks ✅

---

## 🔥 Quick Command Reference

```bash
# Navigate to project
cd D:\work\rabetin\rabetin

# Run migration
mysql -u rabetin -pMoody-006M rabetin < design_system_migration.sql

# Verify
mysql -u rabetin -pMoody-006M rabetin -e "SELECT name, category FROM design_templates;"

# Test
# Open: http://localhost/test_templates.php
```

---

**Run that command and you're all set!** 🚀

