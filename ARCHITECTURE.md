# Architecture sfUpload 1.2.0

## 🏗️ Diagramme d'architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    APPLICATION WEB                          │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ↓
        ┌──────────────────────────────────────┐
        │    BRIDGE: UploadedFileAdapter       │
        │  (Convertit $_FILES en PSR-7)        │
        └──────────────────┬───────────────────┘
                           │
                           ↓
        ┌──────────────────────────────────────┐
        │     FACADE: Uploader                 │
        │  (Chef d'orchestre)                  │
        └──────────┬───────────────────────────┘
                   │
        ┌──────────┴───────────────┐
        │                          │
        ↓                          ↓
    ┌────────────┐          ┌──────────────┐
    │ VALIDATION │          │ STORAGE      │
    │  Validator │          │ LocalStorage │
    │            │          │              │
    │ • Taille   │          │ • Génère nom │
    │ • MIME     │          │ • Déplace    │
    │            │          │ • Permissions│
    └────────────┘          └──────────────┘
        │ uses                    │
        ↓                         ↓
    ┌──────────────────┐    ┌────────────┐
    │ MimeTypeConstr.  │    │ Filesystem │
    │ (Magic Bytes)    │    │            │
    └──────────────────┘    └────────────┘

        ↓ Result

    ┌──────────────────────────┐
    │ FileInfo (DTO)           │
    │ • originalName           │
    │ • savedName              │
    │ • fullPath               │
    │ • mimeType               │
    │ • size                   │
    │ + getters()              │
    └──────────────────────────┘
```

## 📦 Modules principaux

### 1. **Bridge** - Adaptateurs PSR-7
```
src/Bridge/
└── UploadedFileAdapter.php
    ├── fromGlobal($_FILES)  → PSR-7 UploadedFileInterface
    └── Implémente tous les méthodes requises
```

### 2. **Configuration** - Gestion centralisée
```
src/Configuration/
└── UploadConfig.php
    ├── imageOnly()      → JPEG, PNG, WebP, GIF (10 Mo)
    ├── documentOnly()   → PDF, Word, Excel (20 Mo)
    ├── any()            → Tous les fichiers (50 Mo)
    └── Fluent API       → Chaîner les setters
```

### 3. **Validation** - Sécurité des uploads
```
src/Validation/
├── Validator.php              → Orchestrateur
│   ├── validate()             → Valide taille + MIME
│   └── getErrors()            → Liste des erreurs
│
└── MimeTypeConstraint.php     → Détection MIME
    ├── isValid()              → Vérifie le type
    └── detectMimeType()       → Magic Bytes (finfo)
```

### 4. **Storage** - Stockage physique
```
src/Storage/
└── LocalStorage.php
    ├── __construct($path, $createDir)
    ├── store()                → Déplace le fichier
    ├── generateSecureName()   → Hash aléatoire
    └── getUploadPath()        → Retourne le chemin
```

### 5. **Utility** - Helpers pratiques
```
src/Utility/
└── FileHelper.php
    ├── formatFileSize()       → "5.2 MB"
    ├── getFileType()          → "image", "document"
    ├── sanitizePath()         → Sécurise les chemins
    ├── fileExists()           → Vérifie existence
    └── getFileStats()         → Stats du fichier
```

### 6. **Exception** - Gestion d'erreurs
```
src/Exception/
└── UploadException.php        → Exception métier
```

### 7. **Facade** - Interface principale
```
src/
├── Uploader.php               → Orchestrateur
│   └── upload(UploadedFileInterface): FileInfo
│
└── FileInfo.php               → Résultat (DTO)
    ├── originalName, savedName, fullPath, mimeType, size
    ├── getExtension()
    ├── getFileType()
    ├── getFormattedSize()
    ├── exists()
    └── getStats()
```

## 🔄 Flux d'une requête

```
1. Request POST avec $_FILES['file']
        ↓
2. UploadedFileAdapter::fromGlobal($_FILES['file'])
        ↓ Crée un objet PSR-7
3. $uploader->upload($psr7File)
        ↓
4. Validation:
   ├─ Vérifier absence d'erreur PHP
   ├─ Vérifier taille ≤ maxSize
   └─ Vérifier MIME via Magic Bytes
        ↓ Success
5. LocalStorage::store()
   ├─ Générer nom sécurisé (hash + extension)
   └─ Déplacer fichier temporaire
        ↓
6. Retourner FileInfo(originalName, savedName, etc.)
        ↓ Exception
7. Catch UploadException
   └─ Message d'erreur explicite
```

## 💾 Flux des données

```
┌─────────────────────────┐
│ Requête HTTP POST       │
│ $_FILES['file'] = {     │
│   name: "photo.jpg"     │
│   type: "image/jpeg"    │
│   tmp_name: "/tmp/xxx"  │
│   size: 2097152         │
│   error: 0              │
│ }                       │
└────────────┬────────────┘
             ↓
    ┌────────────────────┐
    │ UploadedFileAdapter│
    └────────────┬───────┘
                 ↓
    ┌────────────────────────┐
    │ Uploader::upload()      │
    │ Validation              │
    │  • Taille: 2 Mo < 5 Mo  │
    │  • MIME: image/jpeg     │
    └────────────┬────────────┘
                 ↓
    ┌────────────────────────┐
    │ LocalStorage::store()   │
    │ • Génère: a1b2c3d4.jpg │
    │ • Déplace vers: /uploads│
    └────────────┬────────────┘
                 ↓
    ┌──────────────────────────┐
    │ FileInfo                 │
    │ {                        │
    │   originalName: "photo"  │
    │   savedName: "a1b2c3d4"  │
    │   fullPath: "/uploads/.."│
    │   mimeType: "image/jpeg" │
    │   size: 2097152          │
    │ }                        │
    └──────────────────────────┘
```

## 🔐 Points de sécurité

```
1. MIME TYPE VALIDATION
   ├─ Utilise Magic Bytes (finfo) pas l'extension
   ├─ Détecte les vrais types de fichier
   └─ Prévient l'upload de malwares déguisés

2. SECURE NAMING
   ├─ Génère hash aléatoire avec random_bytes()
   ├─ Impossible à deviner les noms
   └─ Prévient Path Traversal

3. SIZE VALIDATION
   ├─ Vérifie max_size configuré
   ├─ Prévient Denial of Service
   └─ Protège la bande passante

4. PERMISSIONS
   ├─ Vérifie dossier accessible en écriture
   ├─ Gère les erreurs de système de fichiers
   └─ Empêche les écritures non autorisées
```

## 📊 Cas d'usage

### Simple Upload
```
simple.php → UploadedFileAdapter → Uploader → FileInfo
```

### Upload avec config prédéfinie
```
images-advanced.php → UploadConfig → Uploader → FileInfo
```

### API JSON
```
api-endpoint.php → UploadedFileAdapter → json_encode(FileInfo)
```

### Multi-type
```
multi-type.php → Multiple UploadConfig → Uploader → FileInfo
```

## 🎯 Design Patterns utilisés

| Pattern | Lieu | Usage |
|---------|------|-------|
| **Facade** | `Uploader` | Interface simple pour le système complexe |
| **Adapter** | `UploadedFileAdapter` | Convertit `$_FILES` en PSR-7 |
| **Factory** | `UploadConfig::imageOnly()` | Crée configurations |
| **Data Transfer Object** | `FileInfo` | Transfert de données |
| **Fluent Interface** | `UploadConfig` | Chaîner méthodes |
| **Strategy** | `MimeTypeConstraint` | Stratégies de validation |

## 📈 Complexité

| Aspect | Mesure |
|--------|--------|
| **Lignes de code** | ~600 |
| **Dépendances externes** | 0 (PSR-7 seulement) |
| **Classes principales** | 8 |
| **Interfaces implémentées** | 1 (PSR-7) |
| **Temps de setup** | 2-3 min |
| **Temps d'intégration** | 5 min |

---

Développé avec ❤️ par **fomadev**
