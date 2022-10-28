<?php

namespace App\Services;

class ImageHandler
{
    /**
     * Imagick instance
     */
    private \Imagick $imagick;

    /**
     * Create a new image handler instance
     * 
     * @param  string  $base64_image
     * @return void
     */
    public function __construct(string $base64_image) {
        $image_data = substr($base64_image, strpos($base64_image, ',') + 1);
        $image = base64_decode($image_data);

        $this->imagick = new \Imagick();
        $this->imagick->readImageBlob($image);
    }

    /**
     * Detect cell size in pixels
     * 
     * @return int
     */
    public function detectCellSize(): int
    {
        $image_rows_colors = '';
        $image_width = $this->imagick->getImageWidth();
        $imageIterator = $this->imagick->getPixelIterator();
    
        foreach ($imageIterator as $x => $pixels) {
            $row_white_pixels_count = 0;
    
            foreach ($pixels as $y => $pixel) {
                $color = $this->imagick->getImagePixelColor($x, $y);
                $color_array = $color->getColor();
                $color_sum = array_sum($color_array);
                if ($color_sum >= 765) {
                    $row_white_pixels_count++;
                }
            }
    
            $image_rows_colors .= ($row_white_pixels_count == $image_width) ? 1 : 0;
    
            $imageIterator->syncIterator();
        }
    
        $image_rows_colors_array = explode('1', $image_rows_colors)[1] ?? null;
    
        return !empty($image_rows_colors_array) 
            ? strlen($image_rows_colors_array) 
            : 0;
    }

    /**
     * Detect darkness of each cell
     * 
     * @return array
     */
    public function detectDarkness(): array
    {
        $cells = [];
        $cell_size = $this->detectCellSize();
        
        $cell_darkness_max = $cell_size * $cell_size * (755 + 1);
    
        $imageIterator = $this->imagick->getPixelIterator();
    
        foreach ($imageIterator as $x => $pixels) {
            if ($x % ($cell_size + 1) == 0) {
                continue;
            }
    
            $cell_index_x = floor($x / $cell_size);
    
            foreach ($pixels as $y => $pixel) {
                if ($y % ($cell_size + 1) == 0) {
                    continue;
                }
    
                $color = $this->imagick->getImagePixelColor($x, $y);
                $color_array = $color->getColor();
                $darkness_level = (755 + 1) - array_sum($color_array);
    
                
                $cell_index_y = floor($y / $cell_size);
    
                $cell_index = "x:$cell_index_x|y:$cell_index_y";
                $cells[$cell_index] = ($cells[$cell_index] ?? 0) + $darkness_level;
            }
    
            $imageIterator->syncIterator();
        }

        $cells = array_map(fn($value) => ceil($value / $cell_darkness_max * 100), $cells);
    
        return $cells;
    }

    /**
     * Filter darkest cells
     * 
     * @param  int  $min_level
     * @return array
     */
    public function filterDarkestCells(int $min_level): array
    {
        $cells = $this->detectDarkness();
        $filtered_cells = [];

        foreach ($cells as $key => $value) {
            if ($value > $min_level) {
                $key_data = explode('|', $key);
                $axis_x_data = explode(':', $key_data[0]);
                $axis_y_data = explode(':', $key_data[1]);

                $filtered_cells[] = [
                    'x' => $axis_x_data[1],
                    'y' => $axis_y_data[1],
                    'level' => $value,
                ];
            }
        }

        return $filtered_cells;
    }
}
