# 🚀 Setup Guide: Linktree-Style Design System

## Quick Start

### 1. Run the Database Migration

You need to add new columns to the `profiles` table and insert 20 curated templates.

#### Option A: MySQL Command Line
```bash
mysql -u rabetin -pMoody-006M rabetin < curated_themes_migration.sql
```

#### Option B: phpMyAdmin / Database GUI
1. Open `curated_themes_migration.sql` in a text editor
2. Copy all the SQL
3. Paste into phpMyAdmin SQL tab
4. Click "Go"

#### Option C: Manual SQL Execution
Run this SQL in your MySQL client:

```sql
-- Add new columns
ALTER TABLE profiles 
  ADD COLUMN IF NOT EXISTS button_radius INT DEFAULT 14,
  ADD COLUMN IF NOT EXISTS button_border_color VARCHAR(20) DEFAULT '#667eea',
  ADD COLUMN IF NOT EXISTS button_border_width INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS button_border_style VARCHAR(20) DEFAULT 'solid',
  ADD COLUMN IF NOT EXISTS button_hover_effect TINYINT(1) DEFAULT 1;

-- Templates will be inserted from the full migration file
```

### 2. Test the System

1. Visit your dashboard: `http://rabetin.bio/dashboard/index.php?tab=design`
2. You should see:
   - **Curated** and **Customizable** tabs at the top
   - 20 template cards in the Curated tab
   - Full customization options in Customizable tab

### 3. Apply a Template

1. Click any template card (e.g., "Halloween", "Tech Professional")
2. Click the "✨ Apply Template" button
3. Visit your profile page to see the changes!

---

## 🎨 What's New?

### Dashboard Changes (`dashboard/index.php`)
- ✅ Curated/Customizable mode tabs
- ✅ 20 template cards with visual previews
- ✅ Advanced button customization (radius, border, hover)
- ✅ 24 font options (grouped by category)
- ✅ Improved UX with instant feedback

### Backend Changes (`dashboard/save_design.php`)
- ✅ Handles new customization fields
- ✅ Applies template settings when selected
- ✅ Validates all inputs (colors, dimensions, styles)
- ✅ Supports avatar & background image uploads

### Profile Rendering (`profile.php`)
- ✅ Dynamic button borders (color, width, style)
- ✅ Custom border radius support
- ✅ Hover effect toggle
- ✅ 24 Google Fonts loaded
- ✅ All new card styles (including "outlined")

### Database (`curated_themes_migration.sql`)
- ✅ 5 new `profiles` columns for advanced customization
- ✅ 20 curated templates across 5 categories
- ✅ Seasonal templates (Halloween, Christmas, etc.)
- ✅ Industry-specific templates (Tech, Gaming, Music, etc.)
- ✅ Background images (Unsplash CDN)

---

## 📦 Files Modified

| File | Changes |
|------|---------|
| `dashboard/index.php` | Added Curated/Customizable tabs, 20+ fonts, advanced button options |
| `dashboard/save_design.php` | New fields validation & saving logic |
| `profile.php` | Renders custom borders, radius, hover effects, new fonts |
| `curated_themes_migration.sql` | New database columns + 20 templates |
| `LINKTREE_DESIGN_SYSTEM.md` | Full documentation of the system |

---

## ✅ Verification Checklist

After running the migration, verify:

- [ ] Dashboard Design tab loads without errors
- [ ] You see "Curated" and "Customizable" tabs
- [ ] 20 template cards appear in Curated tab
- [ ] Font dropdown has 24 options
- [ ] Button customization has radius, border width, border style fields
- [ ] Clicking a template shows "Apply Template" button
- [ ] Applying a template updates your profile
- [ ] Profile page reflects the new design
- [ ] All fonts load correctly

---

## 🐛 Troubleshooting

### "Templates Not Loaded" Error
**Problem**: `design_templates` table doesn't exist  
**Solution**: Run `curated_themes_migration.sql` migration

### "Column not found" Error
**Problem**: New `profiles` columns missing  
**Solution**: Run the `ALTER TABLE` statements from migration

### Templates Don't Apply
**Problem**: Database connection or template fetch error  
**Solution**: Check `dashboard/save_design.php` for PHP errors in logs

### Fonts Not Loading
**Problem**: Google Fonts link broken  
**Solution**: Check `profile.php` line 123 - ensure Google Fonts URL is correct

### MySQL Command Not Found (Windows)
**Problem**: `mysql` not in PATH  
**Solution**: 
1. Find your MySQL installation (e.g., `C:\xampp\mysql\bin\mysql.exe`)
2. Use full path: `C:\xampp\mysql\bin\mysql.exe -u rabetin -pMoody-006M rabetin < curated_themes_migration.sql`
3. Or add MySQL to PATH environment variable

---

## 🎯 Next Steps

Once everything is working:

1. **Explore Templates**: Try different seasonal themes
2. **Customize**: Use Customizable tab for unique designs
3. **Test Mobile**: Templates are responsive - check on phone
4. **Share**: Show users the new design options!
5. **Monitor**: Check which templates are most popular (add analytics)

---

## 📚 Additional Resources

- **Full Documentation**: See `LINKTREE_DESIGN_SYSTEM.md`
- **Template Visuals**: Check `TEMPLATES_VISUAL_GUIDE.md`
- **Migration SQL**: Review `curated_themes_migration.sql`

---

## 🆘 Need Help?

If you encounter issues:

1. Check browser console for JavaScript errors
2. Check PHP error logs for backend issues
3. Verify database schema matches migration
4. Test with a clean browser cache
5. Try a different template to isolate issues

---

**Happy Designing! 🎨✨**

Your bio link platform is now on par with Linktree!

