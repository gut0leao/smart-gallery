# Smart Gallery Filter - Demo Data

This directory contains scripts and assets for managing demo data for the Smart Gallery Filter plugin.

## 📁 Directory Structure

```
demo-data/
├── README.md           # This documentation
├── images/            # Car images for demo data (196 images)
├── pods-import.php    # PHP script for importing demo data
├── pods-import.sh     # Bash script wrapper for import
├── pods-reset.php     # PHP script for resetting demo data  
└── pods-reset.sh      # Bash script wrapper for reset
```

## 🚀 Usage

### Import Demo Data

Imports complete demo data including:
- Car and Dealer custom post types
- Related taxonomies (brands, body types, fuel types, etc.)
- 196 sample cars with featured images
- 5 sample dealers
- Proper taxonomy associations

```bash
./demo-data/pods-import.sh
```

### Reset Demo Data

Completely removes all demo data:
- All car and dealer posts
- All demo taxonomies and terms
- Pods configurations for demo CPTs
- Associated media files

```bash
./demo-data/pods-reset.sh
```

## 📋 Requirements

- WordPress with DDEV environment
- Pods Framework plugin active
- Smart Gallery Filter plugin active
- Sufficient disk space for 196 car images

## 🔧 Technical Details

### Import Process
1. Creates Car and Dealer CPTs with Pods
2. Creates and configures taxonomies (including hierarchical car_location)
3. **Creates hierarchical location structure**: Country → State/Region → City
4. Generates realistic car data from image filenames
5. Associates cars with dealers based on brand specializations
6. Uploads and associates WordPress native featured images
7. Fixes taxonomy associations with CPTs

### Reset Process
1. Deletes all car and dealer posts
2. Removes demo taxonomies and terms
3. Cleans up Pods configurations
4. Removes orphaned media files
5. Flushes rewrite rules

## 🎯 Demo Data Overview

**Cars**: 196 vehicles with:
- Realistic prices ($15,000 - $150,000)
- Years (1990-2024)
- Mileage (5,000 - 80,000 miles)
- Engine specs and features
- Associated with dealers by location/brand specialization
- WordPress native featured images

**Dealers**: 5 dealerships with specializations:
- Premium Motors (New York) - Luxury brands (BMW, Mercedes-Benz, Audi)
- City Auto Center (California) - Popular brands (Toyota, Honda, Nissan)
- Sports Car Depot (Florida) - Sports cars (Ferrari, Porsche, Lamborghini)  
- Family Auto Sales (Texas) - Family vehicles (Ford, Chevrolet, Hyundai)
- Electric Future Motors (Washington) - Eco-friendly (Tesla, Prius, Leaf)

**Taxonomies**:
- Car Brand (~52 terms, shared with dealers)
- Car Body Type (~22 terms)
- Car Fuel Type (4 terms: gasoline, diesel, hybrid, electric)
- Car Transmission (3 terms: automatic, manual, cvt)
- **Car Location** (58 terms, **hierarchical**):
  - 3 Countries: United States, Canada, United Kingdom
  - 11 States/Regions: California, New York, Texas, Florida, Washington, Ontario, Quebec, British Columbia, England, Scotland, Wales
  - 44 Cities: All properly nested under their respective states/regions
- Dealer Location (5 terms)
