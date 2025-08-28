#!/bin/bash

# Script to generate cars.csv from Stanford Cars Dataset image filenames
# Usage: ./generate-cars-csv.sh

IMAGES_DIR="./demo-data/images"
OUTPUT_CSV="./demo-data/cars.csv"

echo "Generating cars.csv from image filenames..."

# CSV header
echo "name,description,featured_image,price,year,mileage,fuel_type,transmission,engine_size,power_hp,color,doors,brand,model,category,status,condition" > "$OUTPUT_CSV"

# Arrays for random data generation
fuel_types=("gasoline" "hybrid" "electric" "diesel" "flex")
transmissions=("automatic" "manual" "cvt")
colors=("white" "black" "silver" "red" "blue" "gray" "green" "brown" "yellow" "orange")
statuses=("available" "sold" "reserved")
conditions=("new" "seminew" "used")

# Engine configurations by brand
declare -A brand_engines
brand_engines[toyota]="1.6 1.8 2.0 2.4 3.5"
brand_engines[honda]="1.5 1.8 2.0 2.4 3.0"
brand_engines[ford]="1.0 1.5 2.0 2.3 3.5 5.0"
brand_engines[chevrolet]="1.4 1.8 2.0 3.6 6.2"
brand_engines[bmw]="2.0 3.0 4.4 6.0"
brand_engines[mercedes-benz]="2.0 3.0 4.0 6.3"
brand_engines[audi]="2.0 3.0 4.0 5.2"
brand_engines[volkswagen]="1.4 1.8 2.0 3.6"
brand_engines[nissan]="1.6 2.0 2.5 3.5"
brand_engines[hyundai]="1.6 2.0 2.4 3.3"

# Power ranges by category
declare -A category_power
category_power[sedan]="150-300"
category_power[suv]="200-400"
category_power[coupe]="200-500"
category_power[convertible]="200-500"
category_power[hatchback]="120-250"
category_power[wagon]="150-300"
category_power[minivan]="180-280"
category_power[van]="150-250"

counter=0

# Process each image file
for image_file in "$IMAGES_DIR"/*.jpg; do
    if [ -f "$image_file" ]; then
        filename=$(basename "$image_file")
        
        # Skip README
        if [[ "$filename" == "README.md" ]]; then
            continue
        fi
        
        # Parse filename: brand-model-category-year.jpg
        # Remove .jpg extension
        name_part=$(echo "$filename" | sed 's/\.jpg$//')
        
        # Extract year (last part after last dash)
        year=$(echo "$name_part" | grep -o '[0-9]\{4\}$')
        
        # Remove year to get brand-model-category
        without_year=$(echo "$name_part" | sed 's/-[0-9]\{4\}$//')
        
        # Extract brand (first part)
        brand=$(echo "$without_year" | cut -d'-' -f1)
        
        # Extract category (last part)
        category=$(echo "$without_year" | awk -F'-' '{print $NF}')
        
        # Extract model (everything between brand and category)
        model=$(echo "$without_year" | sed "s/^$brand-//" | sed "s/-$category$//" | tr '-' ' ')
        
        # Create display name
        display_name=$(echo "$brand $model $category $year" | sed 's/\b\w/\U&/g')
        
        # Generate realistic data based on brand and category
        
        # Price based on brand and year
        case $brand in
            "ferrari"|"lamborghini"|"bugatti"|"mclaren"|"rolls-royce"|"bentley"|"maybach")
                base_price=200000
                price_range=800000
                ;;
            "bmw"|"mercedes-benz"|"audi"|"porsche"|"jaguar"|"aston-martin")
                base_price=40000
                price_range=150000
                ;;
            "toyota"|"honda"|"nissan"|"hyundai"|"ford"|"chevrolet")
                base_price=15000
                price_range=50000
                ;;
            *)
                base_price=20000
                price_range=60000
                ;;
        esac
        
        # Adjust price by year (newer = more expensive)
        year_factor=$(echo "scale=2; ($year - 1990) / 25" | bc -l)
        price=$(echo "$base_price + ($price_range * $year_factor) + ($RANDOM % 10000)" | bc | cut -d'.' -f1)
        
        # Mileage based on year
        max_mileage=$(echo "scale=0; (2025 - $year) * 12000" | bc)
        if [ "$max_mileage" -lt 0 ]; then max_mileage=5000; fi
        mileage=$(($RANDOM % $max_mileage))
        
        # Engine size based on brand
        if [[ -n "${brand_engines[$brand]}" ]]; then
            engine_size=$(echo "${brand_engines[$brand]}" | tr ' ' '\n' | shuf | head -1)
        else
            engine_size=$(echo "1.6 2.0 2.4 3.0" | tr ' ' '\n' | shuf | head -1)
        fi
        
        # Power based on category and engine
        if [[ -n "${category_power[$category]}" ]]; then
            power_range="${category_power[$category]}"
            min_power=$(echo "$power_range" | cut -d'-' -f1)
            max_power=$(echo "$power_range" | cut -d'-' -f2)
            power_hp=$(($min_power + $RANDOM % ($max_power - $min_power)))
        else
            power_hp=$((150 + $RANDOM % 200))
        fi
        
        # Fuel type based on year and brand
        if [ "$year" -ge 2015 ]; then
            if [[ "$brand" == "tesla" ]]; then
                fuel_type="electric"
            elif [[ "$brand" == "toyota" || "$brand" == "honda" ]] && [ $(($RANDOM % 3)) -eq 0 ]; then
                fuel_type="hybrid"
            else
                fuel_type=${fuel_types[$RANDOM % ${#fuel_types[@]}]}
            fi
        else
            fuel_type="gasoline"
        fi
        
        # Transmission based on category and year
        if [[ "$category" == "coupe" || "$category" == "convertible" ]] && [ $(($RANDOM % 3)) -eq 0 ]; then
            transmission="manual"
        elif [ "$year" -ge 2010 ]; then
            transmission=${transmissions[$RANDOM % ${#transmissions[@]}]}
        else
            transmission="automatic"
        fi
        
        # Doors based on category
        case $category in
            "coupe"|"convertible")
                doors=2
                ;;
            "suv"|"minivan"|"wagon")
                doors=5
                ;;
            *)
                doors=4
                ;;
        esac
        
        # Random attributes
        color=${colors[$RANDOM % ${#colors[@]}]}
        status=${statuses[$RANDOM % ${#statuses[@]}]}
        condition=${conditions[$RANDOM % ${#conditions[@]}]}
        
        # Generate description
        description="<p>Excellent <strong>$display_name</strong> in pristine condition.</p><p><strong>Specifications:</strong></p><ul><li>Engine: ${engine_size}L ${fuel_type}</li><li>Power: ${power_hp} HP</li><li>Transmission: $transmission</li><li>Doors: $doors</li><li>Color: $color</li></ul><p>Well-maintained vehicle with complete service history and documentation.</p>"
        
        # Add to CSV (escape quotes in description)
        escaped_description=$(echo "$description" | sed 's/"/\\"/g')
        echo "\"$display_name\",\"$escaped_description\",\"$filename\",$price,$year,$mileage,$fuel_type,$transmission,$engine_size,$power_hp,$color,$doors,$brand,\"$model\",$category,$status,$condition" >> "$OUTPUT_CSV"
        
        echo "[$((counter + 1))] Added: $display_name"
        counter=$((counter + 1))
        
        # Limit to 100 for demo
        if [ $counter -ge 100 ]; then
            break
        fi
    fi
done

echo ""
echo "✅ CSV generated: $OUTPUT_CSV"
echo "📊 Total cars: $counter"
echo ""
echo "Sample entries:"
head -3 "$OUTPUT_CSV"
