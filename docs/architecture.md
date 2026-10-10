# Architecture

## 1. Vue d'ensemble du monorepo

```mermaid
---
id: e1ea9e04-10c8-4209-ac70-97208db83e3f
---
flowchart TD
    subgraph ROOT["Racine Laravel"]
        APP["app/<br/>shell fin (middleware, providers)"]
        BOOT["bootstrap/<br/>app.php · providers.php"]
        CFG["config/<br/>concord · themes · elasticsearch"]
        ROUTES["routes/web.php · console.php"]
        DB["database/migrations · seeders"]
        VITE["vite.config.js"]
    end

    subgraph PKG["packages/Webkul — 38 packages"]
        direction LR
        SHOP["Shop<br/>boutique"]
        ADMIN["Admin<br/>back-office"]
        CORE["Core<br/>helpers, config, jobs"]
        PROD["Product<br/>types & indexers"]
        CAT["Category<br/>nested set"]
        ATTR["Attribute<br/>EAV"]
        CHK["Checkout<br/>panier"]
        SALES["Sales<br/>commandes"]
        CUST["Customer"]
        PAY["Payment / Paypal<br/>Stripe / Razorpay / PayU"]
        SHIP["Shipping"]
        INV["Inventory"]
        CR["CartRule / CatalogRule / Rule"]
        TAX["Tax"]
        MKT["Marketing / Sitemap / CMS"]
        DATAGRID["DataGrid / DataTransfer"]
        MISC["RMA · GDPR · FPC · ImageCache<br/>MagicAI · Theme · User · Installer<br/>SocialLogin · SocialShare · Notification<br/>DebugBar · BookingProduct"]
    end

    ROOT --> PKG
    PKG --> DB
    PKG --> VITE

    style SHOP fill:#e8f5e9
    style ADMIN fill:#e3f2fd
    style CORE fill:#fff3e0
    style SALES fill:#fce4ec
```

Chaque package suit la même structure interne :

```mermaid
flowchart LR
    A["Config/<br/>acl · system · admin-menu"] --> B["Contracts/<br/>interfaces"]
    B --> C["Models + Proxy<br/>et Repositories"]
    C --> D["Http/<br/>Controllers · Requests · Middleware"]
    D --> E["Resources/<br/>views · lang (21 locales) · assets"]
    F["DataGrids/"] --> D
    G["Database/<br/>Migrations · Factories · Seeders"] --> C
    H["Providers/<br/>ServiceProvider + ModuleServiceProvider"] --> A
    I["Routes/<br/>admin-routes · shop-routes"] --> D
```

## 2. Couches applicatives

```mermaid
flowchart TD
    U["Utilisateur<br/>navigateur"] --> MW

    subgraph MW["Middlewares"]
        M1["web"]
        M2["shop : locale · currency · theme"]
        M3["admin : Bouncer (ACL), TwoFactor"]
        M4["cache.response (FPC)"]
        M5["PreventRequestsDuringMaintenance"]
    end

    MW --> CTRL

    subgraph CTRL["Http / Controllers"]
        SC["Shop<br/>HomeController · OnepageController<br/>ProductsCategoriesProxy"]
        AC["Admin<br/>Catalog · Sales · Settings<br/>Marketing · Reporting"]
        API["Shop API<br/>/api/cart · /api/categories<br/>/api/checkout · /api/products"]
    end

    CTRL --> REPO

    subgraph REPO["Repositories (Prettus L5)"]
        R1["CategoryRepository"]
        R2["ProductRepository"]
        R3["OrderRepository"]
        R4["CartRepository"]
        RN["… 40+ repositories"]
    end

    REPO --> MODEL

    subgraph MODEL["Models Eloquent (Concord Proxy)"]
        P1["Category<br/>(nested set _lft/_rgt)"]
        P2["Product (EAV)"]
        P3["Order / Invoice<br/>Shipment / Refund"]
        P4["Cart / CartItem"]
    end

    MODEL --> IDX["Indexers<br/>product_flat · price_indices<br/>inventory_indices"]
    IDX --> DB[("MariaDB")]

    IDX -.-> ES[("Elasticsearch<br/>optionnel")]
    CACHE[("Cache file / Redis<br/>actuel : file")] --- CTRL
```

## 3. Cycle d'une requête (boutique)

```mermaid
sequenceDiagram
    participant B as Navigateur
    participant V as Assets Vite (Vue 3)
    participant R as Routeur Laravel
    participant MW as Middleware shop
    participant C as Controller
    participant RE as Repository
    participant DB as MariaDB

    B->>V: GET / (charge app-*.js + css)
    V->>R: XHR /api/categories, /api/cart…
    R->>MW: web + shop (locale, currency, theme)
    MW->>MW: résout channel via hostname
    MW->>C: dispatch
    C->>RE: getVisibleCategoryTree(root_category_id)
    RE->>DB: select * from categories (nested set)
    DB-->>RE: lignes
    RE-->>C: arbre
    C-->>B: 200 HTML / JSON
    V-->>B: rendu Vue (menu, produits)
```

## 4. Pipeline de build frontend

```mermaid
flowchart LR
    subgraph SRC["Sources"]
        S1["resources/js · resources/css"]
        S2["packages/Webkul/Shop/src/Resources/assets"]
        S3["packages/Webkul/Admin/src/Resources/assets"]
    end

    S1 -->|npm run build| O1["public/build/"]
    S2 -->|npm run build<br/>dans packages/Webkul/Shop| O2["public/themes/shop/default/build/"]
    S3 -->|npm run build<br/>dans packages/Webkul/Admin| O3["public/themes/admin/default/build/"]

    O1 --> M["manifest.json"]
    O2 --> M2["manifest.json"]
    O3 --> M3["manifest.json"]

    M --> BL["@vite / ViteManifest"]
    M2 --> BL
    M3 --> BL
    BL --> HTML["Blade : balises link / script injectées"]
```

> `public/themes/*/build/` est en `.gitignore` côté Bagisto standard mais **présent ici**
> (artefacts déjà compilés). Modifier un fichier source impose de recompiler le thème concerné.

## 5. Système de modules Concord

```mermaid
flowchart LR
    A["Contract<br/>Webkul\\Sales\\Contracts\\Order"] --> B["Model<br/>Webkul\\Sales\\Models\\Order"]
    B --> C["Proxy<br/>Concord::model ..."]
    C --> D["config/concord.php<br/>+ ModuleServiceProvider"]
    D --> E["Injection par interface<br/>dans Controllers / Repositories"]
    E --> F["Remplacement possible<br/>par un module custom"]
```

## 6. Points d'extension du projet

| Besoin | Où intervenir |
|---|---|
| Modification boutique (Vue/Blade) | `packages/Webkul/Shop/src/Resources` → recompiler |
| Modification admin | `packages/Webkul/Admin/src/Resources` → recompiler |
| Nouvelles tables | `database/migrations/` (racine) ou `packages/Webkul/*/src/Database/Migrations` |
| Nouveau package | Contract + Model + Proxy + Repository, enregistrer dans `bootstrap/providers.php` **et** `config/concord.php` |
| Config système admin | `packages/Webkul/*/src/Config/system.php` |
| ACL admin | `packages/Webkul/*/src/Config/acl.php` |
| Menu admin | `packages/Webkul/*/src/Config/admin-menu.php` |
