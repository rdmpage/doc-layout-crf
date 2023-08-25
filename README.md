# Document layout with CRF

Use a CRF to label document layout. Inspired by Grobid, input and output data formats based on VILA (a LLM approach).

## Training data

Need a CSV with information on each file used for training, particularly source URL and license.

Possible files

ID | DOI | URL | License | PDF
--|--|--|--
zt01991p027 | 10.11646/zootaxa.1991.1.1 | | “free” | https://www.mapress.com/zootaxa/2009/f/zt01991p027.pdf
zt03796p593 | 10.11646/zootaxa.3796.3.10 | | “free” | https://www.mapress.com/zootaxa/2014/f/zt03796p593.pdf
PK-184-067_article-71045_en_1 | 10.3897/phytokeys.184.71045 | |CC-BY | https://phytokeys.pensoft.net/article/71045/download/pdf/
Cassidafromborneo | | | “free” | http://www.cassidae.uni.wroc.pl/Cassidafromborneo.pdf
Minkina_Kral_2022_Rhyparus_ASZB | | | “free” | https://www.zoospol.cz/wp-content/uploads/2022/12/Minkina_Kral_2022_Rhyparus_ASZB.pdf
129ebinger_new_senegalia | | | “open access” | https://www.phytologia.org/uploads/2/3/4/2/23422706/99_2_126-129ebinger_new_senegalia.pdf
proccas_v58_n08 | | | “free” | https://researcharchive.calacademy.org/research/scipubs/pdfs/v58/proccas_v58_n08.pdf
Kral_et_al_Enoplotrupes-Enoplotrupes-apatani-sp.-nov | | | “free” | https://www.zoospol.cz/wp-content/uploads/2021/05/
s6 | 10.5343/bms.2017.1119 | | “Free content” | https://www.ingentaconnect.com/search/download?pub=infobike://umrsmas/bullmar/2018/00000094/00000001/art00006&mimetype=application/pdf
S26 | | https://www.ingentaconnect.com/contentone/umrsmas/bullmar/2002/00000071/00000002/art00026 | “Free content” | https://www.ingentaconnect.com/search/download?pub=infobike://umrsmas/bullmar/2002/00000071/00000002/art00026&mimetype=application/pdf 
| | 10.11646/zootaxa.5336.2.2 | https://www.mapress.com/zt/article/view/zootaxa.5336.2.2/51703 | CC-BY-NC | 
7459 | 10.17109/AZH.68.1.23.2022 | https://ojs.mtak.hu/index.php/actazool/article/view/7459| CC-BY-NC | https://ojs.mtak.hu/index.php/actazool/article/view/7459/6676
ActaZH_2017_Vol_63_4_429 | 10.17109/AZH.63.4.429.2017 | https://ojs.mtak.hu/index.php/actazool/article/view/948 | CC-BY-NC | http://actazool.nhmus.hu/63/4/ActaZH_2017_Vol_63_4_429.pdf
ActaZH_2017_Vol_63_1_71 | 10.17109/AZH.63.1.71.2017 | https://ojs.mtak.hu/index.php/actazool/article/view/1274 | CC-BY-NC | http://actazool.nhmus.hu/63/1/ActaZH_2017_Vol_63_1_71.pdf 
ActaZH_2017_Vol_63_4_377| 10.17109/AZH.63.4.377.2017 | https://ojs.mtak.hu/index.php/actazool/article/view/1170 | CC-BY-NC| http://actazool.nhmus.hu/63/4/ActaZH_2017_Vol_63_4_377.pdf
ZM1989063006 | | https://repository.naturalis.nl/pub/318136 | CC-BY | https://repository.naturalis.nl/pub/318136/ZM1989063006.pdf

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



