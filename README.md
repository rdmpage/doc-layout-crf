# Document layout with CRF

Use a CRF to label document layout. Inspired by Grobid, input and output data formats based on VILA (a LLM approach).

## MAJOR fuck up

Will need to handle blank pages as they screw with the order of data in the data and output files, resulting in major mess :(

## Issues

### Tesseract can badly fail to recognise image boundaries

e.g. BioStor 260594 has some figures badly mangled, recognised as a mixture of text and image fragments, text typically has low confidence, but still an ugly mess.

#### Layout Parser may be the answer

Layout Parser  seems to be able to identify figure blocks accurately, so we could use it to detect figures, extract those blocks, add to hOCR-derived blocks, deleting anything in hOCR that overlaps those new figure blocks, then go from there.

### Languages and Unicode

Rather than detect languages we could rely on Unicode code blocks as a way to signal that we have different languages. We can use `IntlChar::getBlockCode($char)` to detect the block, for example:

| language | character | block |
|--|--|--|--|
|en | A  | 1|
|zu |河  | 71|
|de  | ö  | 2|
|ru  | Ж  | 9|
|ja  | ク  | 63|
|ko  | 가  | 74|
|fr  | é  | 2|
|no |  ø  | 2|
|?  | ♂  | 55|

### OCR training

Installed tesseract using Homebrew, works well. Script `ocr.php` will OCR a PDF to generate tokens files, at which point the other tools can be used. It also extracts figures flagged in the hOCR output.

One issue is the failure to recognise male/female symbols. There are articles about training a new font, but apparently we can also add symbols to an existing training data (but it depends on  a particular format being used). See the `tess` folder for a bunch of stuff on this topic.

### Tools

By default I use [pdf2xml](https://github.com/rdmpage/pdf2xml), but other tools are available.

The popplr took, e.g.:

```
pdftohtml -xml 32_2_113_118_Golovatch_Korotaeva_for_Inet.pdf x.xml
```

[pdfalto](https://github.com/kermitt2/pdfalto) (see https://github.com/kermitt2/pdfalto/issues/159 for my experience building this on a Mac M1) is used by GROBID and handles superscripts properly (I think).


### Fonts

Some files, e.g. 32_2_113_118_Golovatch_Korotaeva_for_Inet.pdf have custom fonts for male/female symbols. These fonts can be seen using Adobe Acrobat, and extracted using:

```
mutool extract 32_2_113_118_Golovatch_Korotaeva_for_Inet.pdf
```

I need to understand how to add fonts to whatever PDF tool I use. It is possible to add fonts to XPDF (see notes for `pdftoxml`). Can we generative this?

Using `php pdf_explore.php` would sometimes crash with an encoding exception, commenting out the line:

```
 //$details['Encoding'] = ($this->has('Encoding') ? (string) $this->get('Encoding') : 'Ansi');
```

in file `/Users/rpage/Development/doc-layout-crf/vendor/smalot/pdfparser/src/Smalot/PdfParser/Font.php(97)`

Looks like issue might be that encoding can be an array, not just a string. An example PDF that crashes `pdf_explore.php` is `0022-1511_2006_40_486_NFSOTG_2.0.CO_2.pdf`

### Superscript/subscript

~~Some files have super- or subscripts, and `pdftoxml` extracts these as separate blocks, which breaks the line of text and hence affects our ability to understand the text. Can we fix this~~?

`pdfalto` seems to handle these OK.

### “Hidden” blocks

Some PDFs, e.g. `1-s2.0-S1631069110002283-main.pdf` seem to have blocks that ar not visible, but which break my code because at least one of their dimensions is zero. What are these, and how do we handle them?

### Vector images

`.vec` files encode SVG, so we need code to render that. Need to figure out clip areas, and how we can merge vector diagrams and text labels that may be assigned to blocks. See `vec.php` for first attempt at code.

The vector files are linked to the main XML file like this:

```xml
 <xi:include xmlns:xi="http://www.w3.org/2001/XInclude" href="02c0376d7b6e8e00c9c5d622533ef99ddedd3601.xml_data/image-8.vec" />
```

The coordinates seem to be w.r.t. to whole page, and the image may include things like lines in tables, below headers, etc.

#### pdfalto

`pdfalto` extracts vector images as SVG.

### PDFs with no text just outlines

Flattened PDFs have text striped out and replaced by outlines (which `pdfalto` extracts as SVG paths). This means there is no editable nor retrievable text form the PDF, so OCR is the only option, even though PDF is born digital. Such PDFs have images embedded as Paula, so we can still extract those in high quality using `pdftoxml/pdfalto`, but the text will have to be OCRed.

Alternatively, can we read each character path and convert it to a letter, e.g. by transforming to the same coordinate space, matching identical coordinates, then dumping a list and mapping manually.

### Online tools

https://www.eecis.udel.edu/~compbio/PDFigCapX (see https://doi.org/10.1093/bioinformatics/btz228 )

### PDF types and vendors

#### ResearchGate

ResearchGate PDFs seem to have a `rgid` field inserted into the `details` object for a PDF. WE can use this as a flag for the presence of a cover page.

## Training data

Need a CSV with information on each file used for training, particularly source URL and license.

Possible files

Journal | ID | DOI | URL | License | PDF
--|--|--|--|--
Zootaxa | zt01991p027 | 10.11646/zootaxa.1991.1.1 | | “free” | https://www.mapress.com/zootaxa/2009/f/zt01991p027.pdf
 Zootaxa| zt03796p593 | 10.11646/zootaxa.3796.3.10 | | “free” | https://www.mapress.com/zootaxa/2014/f/zt03796p593.pdf
Phytokeys | PK-184-067_article-71045_en_1 | 10.3897/phytokeys.184.71045 | |CC-BY | https://phytokeys.pensoft.net/article/71045/download/pdf/
Genus | Cassidafromborneo | | | “free” | http://www.cassidae.uni.wroc.pl/Cassidafromborneo.pdf
Acta Soc. Zool. Bohem. | Minkina_Kral_2022_Rhyparus_ASZB | | | “free” | https://www.zoospol.cz/wp-content/uploads/2022/12/Minkina_Kral_2022_Rhyparus_ASZB.pdf
Phytologia | 99_2_126-129ebinger_new_senegalia | | | “open access” | https://www.phytologia.org/uploads/2/3/4/2/23422706/99_2_126-129ebinger_new_senegalia.pdf
Proceedings of the California Academy of Sciences | proccas_v58_n08 | | | “free” | https://researcharchive.calacademy.org/research/scipubs/pdfs/v58/proccas_v58_n08.pdf
Acta Soc. Zool. Bohem. | Kral_et_al_Enoplotrupes-Enoplotrupes-apatani-sp.-nov | | | “free” | https://www.zoospol.cz/wp-content/uploads/2021/05/
Bulletin of Marine Science | s6 | 10.5343/bms.2017.1119 | | “Free content” | https://www.ingentaconnect.com/search/download?pub=infobike://umrsmas/bullmar/2018/00000094/00000001/art00006&mimetype=application/pdf
Bulletin of Marine Science  | S26 | | https://www.ingentaconnect.com/contentone/umrsmas/bullmar/2002/00000071/00000002/art00026 | “Free content” | https://www.ingentaconnect.com/search/download?pub=infobike://umrsmas/bullmar/2002/00000071/00000002/art00026&mimetype=application/pdf 
Zootaxa | | 10.11646/zootaxa.5336.2.2 | https://www.mapress.com/zt/article/view/zootaxa.5336.2.2/51703 | CC-BY-NC | 
Acta Zoologica Academiae Scientiarum Hungaricae  | 7459 | 10.17109/AZH.68.1.23.2022 | https://ojs.mtak.hu/index.php/actazool/article/view/7459| CC-BY-NC | https://ojs.mtak.hu/index.php/actazool/article/view/7459/6676
Acta Zoologica Academiae Scientiarum Hungaricae  | ActaZH_2017_Vol_63_4_429 | 10.17109/AZH.63.4.429.2017 | https://ojs.mtak.hu/index.php/actazool/article/view/948 | CC-BY-NC | http://actazool.nhmus.hu/63/4/ActaZH_2017_Vol_63_4_429.pdf
Acta Zoologica Academiae Scientiarum Hungaricae  | ActaZH_2017_Vol_63_1_71 | 10.17109/AZH.63.1.71.2017 | https://ojs.mtak.hu/index.php/actazool/article/view/1274 | CC-BY-NC | http://actazool.nhmus.hu/63/1/ActaZH_2017_Vol_63_1_71.pdf 
Acta Zoologica Academiae Scientiarum Hungaricae  | ActaZH_2017_Vol_63_4_377| 10.17109/AZH.63.4.377.2017 | https://ojs.mtak.hu/index.php/actazool/article/view/1170 | CC-BY-NC| http://actazool.nhmus.hu/63/4/ActaZH_2017_Vol_63_4_377.pdf
ZOOLOGISCHE MEDEDELEMGEN | ZM1989063006 | | https://repository.naturalis.nl/pub/318136 | CC-BY | https://repository.naturalis.nl/pub/318136/ZM1989063006.pdf
The Taxonomic Report | ttr-8-2 | | https://digitalcommons.unl.edu/taxrpt/27/|CC-BY-SA-NC | https://lepsurvey.carolinanature.com/ttr/ttr-8-2.pdf
Entomotaxonomia | 2016001 |10.11680/entomotax.2016001 |http://xbkcflxb.cnjournals.com/xbkcflxben/ch/reader/view_abstract.aspx?file_no=2016001&flag=1 | | http://xbkcflxb.cnjournals.com/xbkcflxben/ch/reader/create_pdf.aspx?file_no=2016001&year_id=2016&quarter_id=1&falg=1
Entomotaxonomia | 2016005 | 10.11680/entomotax.2016005 | | | 
Austrobaileya | ngugi-pomax-ammophila-austrobaileya-v12-107-116 | | https://www.qld.gov.au/environment/plants-animals/plants/herbarium/austrobaileya | “free” | https://www.qld.gov.au/__data/assets/pdf_file/0022/332419/ngugi-pomax-ammophila-austrobaileya-v12-107-116.pdf
Folia Parasitologica | fp.2015.025 | 10.14411/fp.2015.025 | https://folia.paru.cas.cz/artkey/fol-201501-0025_an_additional_genus_and_two_additional_species_of_forticulcitinae_digenea_haploporidae.php | CC-BY | https://folia.paru.cas.cz/pdfs/fol/2015/01/25.pdf
Journal of Species Research | JAKO201515337344310 | 10.12651/JSR.2015.4.1.001 | http://koreascience.or.kr/article/JAKO201515337344310.page | | http://koreascience.or.kr/article/JAKO201515337344310.pdf
Kew Bulletin | s12225-015-9569-6 | 10.1007/S12225-015-9569-6 | https://link.springer.com/article/10.1007/s12225-015-9569-6 | Provided by the Springer Nature SharedIt content-sharing initiative | https://link.springer.com/content/pdf/10.1007/s12225-015-9569-6.pdf?pdf=button%20sticky
Comptes Rendus Biologies | 1-s2.0-S1631069110002283-main | 10.1016/j.crvi.2010.09.005 | https://www.sciencedirect.com/science/article/pii/S1631069110002283 | http://www.elsevier.com/open-access/userlicense/1.0/ | https://www.sciencedirect.com/science/article/pii/S1631069110002283/pdfft?md5=2faf9c3274158814cb171660f3f0db87&pid=1-s2.0-S1631069110002283-main.pdf


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



