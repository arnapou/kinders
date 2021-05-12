Kinders
====


### Informations

Ce repo est le code source de http://kinders.arnapou.net/

Le code est ouvert, vous pouvez l'utiliser comme vous voulez, mais je ne supporterai 
pas les éventuels problèmes que vous auriez.

En bref, il s'agit d'un site de gestion de collection de kinders surprise, collection
que fait mon épouse.

### Changelog versions techniques

| Date       | Branche | Symfony | Php | 
|------------|---------|---------|-----|
| 10/05/2021 | master  | 4.4     | 8.0 |
| 09/05/2020 | v2.x    | 4.4     | 7.4 |
| 14/04/2019 | v1.x    | 4.2     | 7.2 |

### Schema basique des liaisons des entités

```
                                         ╭──╮
                                         │  ▼      ╭──── BPZ ◀────╮
Collection ◀─────╮                ╭──── Kinder ◀───┤              │
                 ├───── Serie ◀───┤         ▲      ╰──── ZBA ◀────┤
Country ◀────────╯                │         │                     │   
                                  │         ╰─────────────────────┤   ╭───▶ Attribute
MenuCategory ◀────── MenuItem     │                               ├───┤
                                  ├───────────────────────────────┤   ╰───▶ Image 
AdminUser                         │                               │     
                                  ├──── Item ◀────────────────────┤
SiteConfig                        │                               │
                                  ╰──── Piece ◀───────────────────╯
```

### Hiérarchie des entités

```
AdminUser
BaseEntity
 ├─ Attribute
 ├─ BaseItem
 │  ├─ BPZ
 │  ├─ Item
 │  ├─ Kinder
 │  ├─ Piece
 │  ├─ Serie
 │  └─ ZBA
 │
 ├─ Collection
 ├─ Country
 ├─ Image
 ├─ MenuCategory
 ├─ MenuItem
 └─ SiteConfig
```

Champs communs de `BaseEntity` :

    int       id
    datetime  createdAt
    datetime  updatedAt
    string    name
    string    slug
    string    comment
    string    description

Champs communs de `BaseItem` :

    int       quantityOwned
    int       quantityDouble
    int       year
    bool      lookingFor
    string    reference
    string    sorting
    string    realsorting
    string    variante
    {}        images          ManyToMany(App\Entity\Image)   
    {}        attributes      ManyToMany(App\Entity\Attribute)
