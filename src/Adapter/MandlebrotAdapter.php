<?php
namespace FractalManager\Adapter;

/*
PM tetrastar fractal generator (C) MC Loonee Dan (Dan Stowell).

This is open source code under the terms of the LGPL Licence.

(v3 - Added ability to specify size of the first tetrastar, and the colours)
(v2 - Not specifying a variable causes it to be randomised)

Variables to be supplied:

$this->iter = number of iterations
$this->size = dimensions of [square] image
$this->ratio = Ratio of size between successive levels
$this->rotate = Rotation (in degrees) between each iteration - multiples of 60 make symmetrical fractal
$firstsize = Size of the first tetrastar. This is in pixels and represents the "radius" -
             the distance from the centre to each of the three points
$this->cola, $this->colb, $this->colc = The fill colours for the tetrastars. Specify these as hex values,
                      without the hash sign in front: eg cola=ff0000&colb=0099ff&colc=ff00ff
*/

class MandlebrotAdapter implements AdapterInterface
{
    const DEFAULT_SIZE = 200;

    public function generate($query)
    {
        mt_srand((double)microtime() * 1000000);

        if (mt_rand(0, 1)) {
            $this->redRand = mt_rand(0, 25);
            $this->greenRand = mt_rand(0, 15);
            $this->blueRand = mt_rand(0, 75);
        } else {
            $this->redRand = mt_rand(210, 255);
            $this->greenRand = mt_rand(210, 255);
            $this->blueRand = mt_rand(210, 255);
        }

        // Ensure variables are initialised, using random numbers if they ain't
        if (!isset($this->iter) || !((int)$this->iter > 0)) {
            $this->iter = mt_rand(3, 8);
        }
        $this->iter = (int)$this->iter;

        if (!isset($this->size) || !((int)$this->size > 0)) {
            $this->size = self::DEFAULT_SIZE;
        }
        $this->size = (int)$this->size;

        if (!isset($this->ratio)) {
            $this->ratio = mt_rand(200, 850) / 1000;
        }
        settype($this->ratio, 'double');

        if (!isset($this->rotate)) {
            $this->rotate = mt_rand(0, 360);
        } else {
            settype($this->rotate, 'double');
        }

        if (!isset($firstsize)) {
            $firstsize = $this->size / 3;
        }
        $firstsize = (int)$firstsize;

        // Initialise the image and its colours
        $this->image = imagecreate($this->size, $this->size);
        $this->fg = ImageColorAllocate($this->image, 0, 0, 0);

        if (isset($this->cola) && strlen($this->cola) == 6) {
            $this->colar = hexdec(substr($this->cola, 0, 2));
            $this->colag = hexdec(substr($this->cola, 2, 2));
            $this->colab = hexdec(substr($this->cola, 4, 2));
            $this->cola = ImageColorAllocate($this->image, $this->colar, $this->colag, $this->colab);
        } else {
            $this->cola = ImageColorAllocate($this->image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
        }
        if (isset($this->colb) && strlen($this->colb) == 6) {
            $this->colbr = hexdec(substr($this->colb, 0, 2));
            $this->colbg = hexdec(substr($this->colb, 2, 2));
            $this->colbb = hexdec(substr($this->colb, 4, 2));
            $this->colb = ImageColorAllocate($this->image, $this->colbr, $this->colbg, $this->colbb);
        } else {
            $this->colb = ImageColorAllocate($this->image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
        }
        if (isset($this->colc) && strlen($this->colc) == 6) {
            $this->colcr = hexdec(substr($this->colc, 0, 2));
            $this->colcg = hexdec(substr($this->colc, 2, 2));
            $this->colcb = hexdec(substr($this->colc, 4, 2));
            $this->colc = ImageColorAllocate($this->image, $this->colcr, $this->colcg, $this->colcb);
        } else {
            $this->colc = ImageColorAllocate($this->image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
        }
        if (isset($this->bg) && strlen($this->bg) == 6) {
            $this->bgr = hexdec(substr($this->bg, 0, 2));
            $this->bgg = hexdec(substr($this->bg, 2, 2));
            $this->bgb = hexdec(substr($this->bg, 4, 2));
            $this->bg = ImageColorAllocate($this->image, $this->bgr, $this->bgg, $this->bgb);
        } else {
            $this->bg = ImageColorAllocate($this->image, $this->redRand, $this->greenRand, $this->blueRand);
        }

        imagefilledrectangle($this->image, 0, 0, $this->size, $this->size, $this->bg); // Colour in the background
        // Set the darn thing going
        $this->color = imagecolorallocate($this->image, $this->redRand, $this->greenRand, $this->blueRand);
        $this->tetrastar($this->size / 2, $this->size / 2, $firstsize, 0);

//        $this->generateSecond();

        return $this->image;
    }

    // The recursive function which will calc & draw tetrastars
    protected function tetrastar($centrex, $centrey, $length, $iteration)
    {
        $xa = $centrex + ($length * sin(deg2rad($this->rotate * $iteration + 60)));
        $ya = $centrey + ($length * cos(deg2rad($this->rotate * $iteration + 60)));

        $xb = $centrex + ($length * sin(deg2rad($this->rotate * $iteration + 180)));
        $yb = $centrey + ($length * cos(deg2rad($this->rotate * $iteration + 180)));

        $xc = $centrex + ($length * sin(deg2rad($this->rotate * $iteration + 300)));
        $yc = $centrey + ($length * cos(deg2rad($this->rotate * $iteration + 300)));

        if ($iteration < $this->iter) {
            // Call function for each of the sub-tetrastars
            $this->tetrastar($xa, $ya, $length * $this->ratio, $iteration + 1);
            $this->tetrastar($xb, $yb, $length * $this->ratio, $iteration + 1);
            $this->tetrastar($xc, $yc, $length * $this->ratio, $iteration + 1);
        }

        // Draw self
        imagefilledpolygon($this->image,
            array((int)$centrex, (int)$centrey, (int)$xa, (int)$ya, (int)$xb, (int)$yb), 3,
            $this->cola);
        imagefilledpolygon($this->image,
            array((int)$centrex, (int)$centrey, (int)$xb, (int)$yb, (int)$xc, (int)$yc), 3,
            $this->colb);
        imagefilledpolygon($this->image,
            array((int)$centrex, (int)$centrey, (int)$xc, (int)$yc, (int)$xa, (int)$ya), 3,
            $this->colc);
        imagepolygon($this->image, array((int)$xa, (int)$ya, (int)$xb, (int)$yb, (int)$xc, (int)$yc),
            3, $this->fg);
    }

    public function generateSecond()
    {
        $min_x = -2;
        $max_x = 1;
        $min_y = -1;
        $max_y = 1;

        $dim_x = self::DEFAULT_SIZE;
        $dim_y = self::DEFAULT_SIZE;

//        $this->color = imagecolorallocate($this->image, 255, 255, 255);
        for ($y = 0; $y <= $dim_y; $y++) {
            for ($x = 0; $x <= $dim_x; $x++) {
                $coef = mt_rand(70, 80) / 100;
                $c1 = ($min_x + ($max_x - $min_x) / $dim_x * $x) * $coef;
                $c2 = ($min_y + ($max_y - $min_y) / $dim_y * $y) * $coef;
                $z1 = 0;
                $z2 = 0;
                for ($i = 0; $i < 100; $i++) {
                    $new1 = $z1 * $z1 - $z2 * $z2 + $c1;
                    $new2 = 2 * $z1 * $z2 + $c2;
                    $z1 = $new1;
                    $z2 = $new2;
                    if ($z1 * $z1 + $z2 * $z2 >= 4) {
                        break;
                    }
                }
                if ($i < 100) {
                    imagesetpixel($this->image, $x, $y, $this->color);
                }
            }
        }

        return $this->image;
    }
}
