# Document layout with CRF

Use a CRF to label document layout. Inspired by Grobid, input and output data formats based on VILA (a LLM approach).

## PDFs

Born digital PDFs need to be converted to VILA format (a set of JSON files, one per page, that lists word and their bounding boxes).

### PDF to XML to JSON

Generate XML for PDF

`pdftoxml -blocks <pdf>`

Then run script `php pdfxml.php <basedir>` where <basedir> is the PDF file name minus the `.pdf` extension. This creates a base directory `<basedir>` with files `tokens<n>.json` that follow the VILA format (with added block information). Each JSON file describes a single page.

## Training

One way to train is to start with some already labelled data (e.g., from VILA or a previous run) and create a new output file: `php labels_to_crf.php > <basedir>.out`.

If you run `php colour.php <basedir>` on a <basedir> with `labels<n>.json` files you will generate HTML where tokens are colour-coded by label. This is a useful way to check that the labels are correct. You can manually edit the `.out` file to fix any bad labels. If you then run `php crf_to_labels.php <basedir> new `labels<n>.json` files will be generated. Rerun `php colour.php <basedir> to check that labels are now correct.

Combine any training data into `rod.train` and run `php train.php`.

### Template file

The template file `rod.template` tells the CRF code how to interpret the data. If this file doesn’t exist it is generated when CRF data is created. If you change the model (e.g., by adding features) you will need to delete `rod.template` to ensure a new, correct template is created.

## Predict

To generate prediction for the files in `<basedir>` run `php predict.php <basedir>`. The resulting labels can be viewed with `php colour.php`.



