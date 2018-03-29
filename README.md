
# Image

Class to manipulate and output images.
 
This class can be used to load or create an image, apply multiple operations, such as scaling and rotation,
and save the resulting image. By default the processed image will be cached and no processing will be
performed when a cached version is available.
 
Example:
```php
$image = new Image();
$image->load("/files/images/samples/PA252134.JPG");
 
// Scale and crop the image so it is exactly 800  600 pixels large.
$image->addOperation(new ImageScaleAndCrop(800, 600));

// Render a text with 80% opacity.
$image->addOperation(new ImageText("Kajona", 300, 300, 40, "rgb(0,0,0,0.8)")
 
// Apply the operations and send the image to the browser.
if (!$image->sendToBrowser()) {
    echo "Error processing image.";
}
```

Custom operations can be added by implementing ImageOperationInterface. Most operations
should inherit from ImageAbstractOperation, which implements ImageOperationInterface
and provides common functionality.
