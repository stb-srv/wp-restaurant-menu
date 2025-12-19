# WP Restaurant Menu

![Version](https://img.shields.io/badge/version-1.7.2-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-green.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)
![License](https://img.shields.io/badge/license-GPL--2.0%2B-red.svg)

Modernes WordPress-Plugin zur Verwaltung von Restaurant-Speisekarten mit Lizenz-Server, Dark Mode, Warenkorb-System und Allergenkennzeichnung.

## \u2728 Features

### Basis-Features (alle Lizenzen)
- \u2705 Men\u00fc-Verwaltung mit Custom Post Type
- \u2705 Kategorien und Men\u00fckarten-Taxonomien
- \u2705 14 EU-Allergene mit Icons
- \u2705 Vegetarisch/Vegan Kennzeichnung
- \u2705 Responsive Grid-Layout (1-3 Spalten)
- \u2705 Suchfunktion
- \u2705 Kategorie-Accordion
- \u2705 Import/Export (JSON)
- \u2705 Gutenberg Block Support

### Premium-Features
- \ud83c\udf19 **Dark Mode** (PRO+, ULTIMATE) - Global oder lokal mit Toggle
- \ud83d\uded2 **Warenkorb-System** (PRO+, ULTIMATE) - Add-to-Cart mit Sidebar
- \u267e\ufe0f **Unlimited Items** (ULTIMATE) - Unbegrenzte Gerichte

## \ud83d\udd11 Lizenz-Modelle

| Modell | Preis | Gerichte | Features |
|--------|-------|----------|----------|
| **FREE** | Kostenlos | 20 | Basis |
| **FREE+** | 15\u20ac einmalig | 60 | Basis |
| **PRO** | 29\u20ac einmalig | 200 | Basis |
| **PRO+** | 49\u20ac einmalig | 200 | Dark Mode + Cart |
| **ULTIMATE** | 79\u20ac einmalig | 900+ | Alle Features |

## \ud83d\ude80 Installation

1. Plugin hochladen und aktivieren
2. `Restaurant Men\u00fc \u2192 Lizenz` \u00f6ffnen
3. Lizenzschl\u00fcssel eingeben (optional)
4. Men\u00fcpunkte erstellen
5. Shortcode `[restaurant_menu]` verwenden

## \ud83d\udcdd Verwendung

### Shortcode

```php
// Alle Gerichte
[restaurant_menu]

// Bestimmte Kategorie
[restaurant_menu category="hauptgerichte"]

// Mit Optionen
[restaurant_menu columns="3" show_search="yes"]
```

### Parameter

- `category` - Kategorie-Filter (Slug)
- `menu` - Men\u00fckarten-Filter (Slug)
- `columns` - Spalten (1-3)
- `show_search` - Suchfeld (yes/no)
- `show_images` - Bilder anzeigen (yes/no)
- `image_position` - Bildposition (top/left)
- `group_by_category` - Nach Kategorien gruppieren (yes/no)

## \ud83d\udcda Dokumentation

Vollst\u00e4ndige Dokumentation siehe [COMPLETE-SPECIFICATION.md](COMPLETE-SPECIFICATION.md)

## \ud83d\udd27 Entwicklung

### Struktur

```
wp-restaurant-menu/
\u251c\u2500\u2500 wp-restaurant-menu.php      # Hauptdatei
\u251c\u2500\u2500 includes/                   # Core Classes
\u251c\u2500\u2500 admin/                      # Backend UI
\u251c\u2500\u2500 assets/                     # CSS/JS
\u251c\u2500\u2500 public/                     # Frontend
\u251c\u2500\u2500 blocks/                     # Gutenberg Blocks
\u2514\u2500\u2500 license-server/             # Lizenz-Server (separat)
```

### Requirements

- WordPress 5.0+
- PHP 7.4+
- MySQL 5.7+

## \ud83d\udc1e Support

- **GitHub Issues**: [github.com/stb-srv/wp-restaurant-menu/issues](https://github.com/stb-srv/wp-restaurant-menu/issues)
- **Email**: s.behncke@icloud.com
- **Website**: [stb-srv.de](https://stb-srv.de)

## \ud83d\udcc4 License

GPL-2.0+ - see [LICENSE](LICENSE)

## \u270f\ufe0f Author

**STB-SRV**  
Website: [stb-srv.de](https://stb-srv.de)  
GitHub: [@stb-srv](https://github.com/stb-srv)

---

**Version**: 1.7.2  
**Last Updated**: December 19, 2024