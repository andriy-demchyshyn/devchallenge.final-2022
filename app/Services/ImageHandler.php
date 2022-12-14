<?php

namespace App\Services;

class ImageHandler
{
    /**
     * Sum of rgb color channels values (255 + 255 + 255)
     */
    const RGB_COLOR_SUM = 765;

    /**
     * Imagick instance
     */
    private \Imagick $imagick;

    /**
     * Create a new image handler instance
     * 
     * @return void
     */
    public function __construct() {
        $this->imagick = new \Imagick();
    }

    /**
     * Add image to processing
     * 
     * @param  string  $base64_image
     * @return void
     */
    public function processImage(string $base64_image): void
    {
        $image_data = substr($base64_image, strpos($base64_image, ',') + 1);
        $this->imagick->readImageBlob(base64_decode($image_data));
    }

    /**
     * Filter darkest cells
     * 
     * @param  int  $min_level
     * @return array
     */
    public function filterDarkestCells(int $min_level): array
    {
        $filtered_cells = [];

        $image_width = $this->imagick->getImageWidth();
        $image_height = $this->imagick->getImageHeight();

        $cell_size = $this->detectCellSize();

        for ($y = 0; ($y + 1) * $cell_size < $image_height; $y++) {
            for ($x = 0; ($x + 1) * $cell_size < $image_width; $x++) {
                $cell_darkness = $this->detectCellDarkness($x, $y, $cell_size);

                if ($cell_darkness > $min_level) {
                    $filtered_cells[] = [
                        'x' => $x,
                        'y' => $y,
                        'level' => $cell_darkness,
                    ];
                }
            }
        }

        return $filtered_cells;
    }

    /**
     * Detect cell size in pixels
     * 
     * @return int
     */
    private function detectCellSize(): int
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
                if ($color_sum >= self::RGB_COLOR_SUM) {
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
     * @param  int  $x
     * @param  int  $y
     * @param  int  $cell_size
     * @return int
     */
    private function detectCellDarkness(int $x, int $y, int $cell_size): int
    {
        $cell_pixel_values = [];

        $cell_darkness_max = $cell_size * $cell_size * self::RGB_COLOR_SUM;

        $area_iterator = $this->imagick->getPixelRegionIterator(($x * ($cell_size + 1) + 1), ($y * ($cell_size + 1) + 1), $cell_size, $cell_size);

        foreach ($area_iterator as $row_iterator) {
            foreach ($row_iterator as $pixel) {
                $colors = $pixel->getColor();
                unset($colors['a']); // Unset alpha-channel
                $cell_pixel_values[] = self::RGB_COLOR_SUM - array_sum($colors);
            }
            $area_iterator->syncIterator();
        }

        return floor(array_sum($cell_pixel_values) / $cell_darkness_max * 100);
    }
}
