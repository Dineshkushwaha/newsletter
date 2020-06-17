# SPH Newsletter

## Installation steps: 

1) Run Composer install which will add the Emogrifier Libary dependency &
hook_event_dispatcher module.

2) Enable hook_event_dispatcher module.
Enable sub module core_event_dispatcher

3) We have created the file to store Emarsys credentials in config folder
with name sph_newsletter.settings.yml. You can add the required details and proceed with the installation.


## Usage

1) Create Newsletter from Newsletter Content Type with required
Emarsys API Param.
2) Preview Web button is to Preview the newsletter on the web.
3) Preview Email button will send test email based on the Preview Segment Id.
4) Launch Email button will send original email based on the Production Segment Id.
5) Edit Article button is to help editors to edit the Article content before sending the 
Newsletter. We have provide the flexibility to change the title, summary and image. (Only applicable to Newsletter).
6) Css File Name field is to provide the css file name.
For eg : newsletter. Do not include .css in the file name.


## Community

sph_newsletter master branch is released at a stable state, but with mileage to go. We are open to pull requests. Please first discuss your intentions via [Issues](https://github.com/sphtech/sph_newsletter/issues).


## Maintainers

* [Dinesh Kushwaha](https://github.com/Dineshkushwaha)
