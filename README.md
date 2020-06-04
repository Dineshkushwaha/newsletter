# SPH Newsletter

Installation steps: 

1) Require hook_event_dispatcher module.
Enable sub module core_event_dispatcher

2) Require Emogrifier Library to convert the internal css
to inline. Use the below command to install

3) We have stored the Emarsys credentials in config folder
with name sph_newsletter.settings.yml. You can add the required details and proceed with the installation.


```bash
composer require pelago/emogrifier
```
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


## Maintainers

* [Dinesh Kushwaha](https://github.com/Dineshkushwaha)