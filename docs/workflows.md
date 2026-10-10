# Workflows métier (séquences Mermaid)

## 1. Page d'accueil de la boutique

```mermaid
sequenceDiagram
    participant N as Navigateur (Vue 3)
    participant H as HomeController
    participant TC as ThemeCustomizationRepository
    participant CR as CategoryRepository
    participant DB as MariaDB

    N->>H: GET /
    activate H
    H->>TC: findWhere(channel_id, type, sort_order)
    TC->>DB: select * from theme_customizations
    DB-->>TC: 13 sections (header-menu, footer, home-content…)
    TC-->>H: sections de la home
    H->>CR: getVisibleCategoryTree(root_category_id)
    CR->>DB: select * from categories (nested set, status=1)
    DB-->>CR: uniquement `Root` (id=1) ⚠
    CR-->>H: arbre [Root]
    H-->>N: 200 + HTML (menu vide car aucun enfant)
    activate N
    N->>N: XHR /api/categories → 1 seule catégorie
```

> **Anomalie courante** : sans catégorie fille, le menu est vide.
> Toute URL type `/lingerie` → `ProductsCategoriesProxyController` → **404**.

## 2. Panier → commande (checkout one-page)

```mermaid
sequenceDiagram
    participant C as Client (SPA)
    participant API as Shop API
    participant Cart as CartRepository<br/>(facade Cart)
    participant One as OnepageController
    participant Pay as Payment
    participant OR as OrderRepository
    participant OI as OrderItemRepository
    participant L as Listeners

    Note over C,Cart: 1. Ajout au panier
    C->>API: POST /api/checkout/cart {product_id, quantity}
    API->>Cart: addProduct()
    Cart->>Cart: collectTotals() (tax, discount, shipping)
    API-->>C: panier mis à jour

    Note over C,One: 2. Adresse
    C->>API: POST /api/checkout/onepage/addresses
    API->>Cart: saveAddress() (billing + shipping)

    Note over C,One: 3. Mode de livraison
    C->>API: POST /api/checkout/onepage/shipping-methods
    API->>Cart: saveShippingMethod()

    Note over C,One: 4. Mode de paiement
    C->>API: POST /api/checkout/onepage/payment-methods
    API->>Cart: savePaymentMethod()

    Note over C,OR: 5. Validation de la commande
    C->>API: POST /api/checkout/onepage/orders
    API->>One: storeOrder()
    One->>Cart: collectTotals()
    One->>One: validateOrder() (stock, adresses, méthodes)
    One->>Pay: getRedirectUrl(cart)
    alt paiement externe (PayPal/Stripe…)
        Pay-->>C: redirect_url (302 vers la passerelle)
    else paiement direct (COD, money transfer)
        One->>OR: create(OrderResource data)
        activate OR
        OR->>OR: Event checkout.order.save.before
        OR->>OR: create order + order_payment
        loop par ligne de commande
            OR->>OI: create order_item (+ children)
            OI->>OI: manageInventory() → product_inventories
            OI->>OI: manageCustomizableOptions()
            OR->>OR: saveLinks() (produits téléchargeables)
        end
        OR->>OR: Event checkout.order.save.after
        OR-->>One: order (id)
        deactivate OR
        Note over OR,L: listeners déclenchés<br/>• Payment · GenerateInvoice (COD → auto-facture)<br/>• Notification · Order (email de confirmation)
        One->>Cart: deActivateCart()
        One-->>C: redirect /checkout/onepage/success
    end
```

## 3. Admin : facture, expédition, remboursement

```mermaid
sequenceDiagram
    participant A as Admin (/admin)
    participant IC as InvoiceController
    participant IR as InvoiceRepository
    participant SC as ShipmentController
    participant SR as ShipmentRepository
    participant RC as RefundController
    participant DB as MariaDB

    Note over A,IC: Facturation
    A->>IC: POST /admin/sales/invoices/{orderId}
    IC->>IC: order.canInvoice() ?
    IC->>IR: validate quantités facturables
    IR->>DB: insert invoices + invoice_items
    IR->>DB: order.status → processing
    IC-->>A: page facture (PDF dompdf)

    Note over A,SC: Expédition
    A->>SC: POST /admin/sales/shipments/{orderId}
    SC->>SR: create() (source de stock, tracking, items)
    SR->>DB: insert shipments + shipment_items
    SR->>DB: product_inventories décrémenté
    SR->>DB: order.status → complete
    SC-->>A: confirmation

    Note over A,RC: Remboursement
    A->>RC: POST /admin/sales/refunds/{orderId}
    RC->>RC: order.canRefund() ?
    RC->>DB: insert refunds + refund_items
    RC->>DB: order.status → closed
```

## 4. Cycle de vie d'une commande

```mermaid
stateDiagram-v2
    [*] --> pending: commande créée
    pending --> processing: paiement accepté / facturé
    pending --> cancelled: annulation / paiement refusé
    processing --> complete: expédiée
    processing --> closed: remboursée
    processing --> cancelled: annulée (stock restitué)
    complete --> closed: remboursement partiel
    cancelled --> [*]
    closed --> [*]

    note right of processing
        manageInventory() à la création
        returnQtyToProductInventory()
        à l'annulation
    end note
```

## 5. Événements métier (`Event::dispatch`)

```mermaid
flowchart LR
    subgraph Avant
        E1["checkout.order.save.before"]
        E2["checkout.order.orderitem.save.before"]
        E3["sales.order.cancel.before"]
        E4["sales.order.update-status.before"]
    end

    subgraph "Création commande (OrderRepository)"
        CR["orders / order_items<br/>manageInventory()"]
    end

    subgraph Après
        E5["checkout.order.save.after"]
        E6["checkout.order.orderitem.save.after"]
        E7["sales.order.cancel.after"]
        E8["sales.order.update-status.after"]
    end

    E1 --> CR --> E5
    E2 --> E6
    E3 --> E7
    E4 --> E8

    E5 --> L1["Payment / Listeners / GenerateInvoice<br/>(auto-facture COD)"]
    E5 --> L2["Notification / Listeners / Order<br/>(email confirmation)"]
    E8 --> L3["Notification / Listeners / Order@updateOrder"]
```

| Événement | Listener | Package |
|---|---|---|
| `checkout.order.save.after` | `Payment\Listeners\GenerateInvoice@handle` | Payment |
| `checkout.order.save.after` | `Notification\Listeners\Order@createOrder` | Notification |
| `sales.order.update-status.after` | `Notification\Listeners\Order@updateOrder` | Notification |
| `bagisto.installed` | `Installer\Listeners\Installer@installed` | Installer |

## 6. Chemin d'une requête produit/catégorie (catch-all)

```mermaid
flowchart TD
    U["URL /xxx"] --> MW["middleware shop<br/>locale · currency · theme"]
    MW --> R{"Route explicite ?"}
    R -->|oui| CTRL["Controller dédié"]
    R -->|non| P["ProductsCategoriesProxyController<br/>{fallbackPlaceholder}"]
    P --> Q1{"catégorie par slug/url_path ?"}
    Q1 -->|oui| PC["page catégorie"]
    Q1 -->|non| Q2{"produit par url_key ?"}
    Q2 -->|oui| PP["page produit"]
    Q2 -->|non| E404["NotFoundHttpException<br/>404 — Route not found"]
    CTRL --> OK["200"]
    PC --> OK
    PP --> OK
```
