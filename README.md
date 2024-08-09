# Views Vanilla JavaScript Lightbox

## Overview
The **Views Vanilla JavaScript Lightbox** module (machine name: `vvjl`) 
enables you to create beautiful and responsive lightbox displays for 
various content items on your Drupal site. This module integrates with 
the Views module, offering a new display style that you can select when 
creating or editing views. It leverages pure vanilla JavaScript for 
smooth and lightweight performance without jQuery dependencies.

## Features
- **Lightbox Format**: Displays content items in a visually appealing 
  lightbox format.
- **Customizable Options**: Provides settings for grid image width, 
  image gap, overlay color, opacity, and animation types.
- **Responsive Design**: Ensures the lightbox adapts seamlessly to 
  different screen sizes.
- **Animation Effects**: Includes various transition animations such as 
  top, bottom, left, right, and zoom.
- **Unique ID Generation**: Automatically generates a unique numeric ID 
  for each view display to ensure consistency and prevent conflicts.

## Configuration
After installing and enabling the module, you can configure it through 
the Views UI:

1. **Grid Image Width**: Set the width of the grid images in pixels.
2. **Grid Image Gap**: Define the gap between grid images in pixels.
3. **Overlay Color**: Choose a color for the lightbox background overlay.
4. **Overlay Opacity**: Set the opacity level for the overlay (from 0.0 
   to 1.0).
5. **Disable Overlay**: Option to completely disable the overlay color.
6. **Animation Type**: Select the type of animation for transitions 
   between images (e.g., Top, Bottom, Left, Right, Zoom).

## Example Usage
1. Create a new view or edit an existing view.
2. Under the "Format" section, select "Views Vanilla JavaScript Lightbox" 
   as the style.
3. Configure the lightbox settings such as grid image width, gap, 
   overlay color, animation type, and more.
4. Save the view and check the lightbox effect on the front end.

## Additional Information
- **Dependencies**: This module does not have any dependencies on other 
  contributed modules.
- **Compatibility**: Fully compatible with Drupal 10 and follows Drupal 
  best practices.

## Installation
1. Download and install the module from 
   [Drupal.org](https://www.drupal.org/project/vvjl).
2. Enable the module through the Drupal admin interface or using Drush: 
   `drush en vvjl`
3. Clear caches to ensure the new plugin is available: `drush cr`

## Troubleshooting
If you encounter any issues or have suggestions for improvements, please 
open an issue in the module's issue queue on Drupal.org.

## Maintainers
- [Alaa Haddad](https://www.drupal.org/u/flashwebcenter)

## Support
For issues, feature requests, and general support, please use the 
[issue queue](https://www.drupal.org/project/issues/vvjl).

## License
This project is licensed under the 
[GNU General Public License, version 2 or later](http://www.gnu.org/licenses/gpl-2.0.html).

---

This file adheres to Drupal best practices for module documentation, 
providing users with a clear understanding of the module's purpose, 
features, and configuration options. It also includes important notes 
on installation, troubleshooting, and support to assist users in 
effectively using the module.
