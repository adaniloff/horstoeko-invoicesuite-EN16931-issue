# Factur-X EN 16931 : XSD -> green, Schematron -> red

A reproducible case demonstrating that a Factur-X invoice can be XSD-valid while violating an
EN 16931 business rule (Schematron) - a violation `horstoeko/invoicesuite` can detect when configured
for the appropriate KoSIT scenario. 

## Context

The French e-invoicing reform mandates the receipt of electronic invoices starting September 1, 2026.
A Factur-X invoice can be perfectly valid according to the XSD schema yet still violate an
EN 16931 business rule (Schematron) that an accredited platform would reject.
`horstoeko/invoicesuite` already supports native XSD validation and includes a generic KoSIT
validator, but its default settings target XRechnung (Germany); this project demonstrates how to
configure it for a pure EN 16931 scenario to expose this secondary validation layer when processing
a French document.

## Demonstration

| Invoice | XSD | KoSIT + EN16931 |
|---|:---:|:---:|
| Valid | ✅ green | ✅ `ACCEPTABLE` |
| Inconsistent (incorrect VAT total) | ✅ green | ❌ `REJECT` - `[BR-CO-15]` |

Full raw output of the two validations for the inconsistent invoice: see below "Proof (step 4)".

## Versions

| Component | Version |
|---|---|
| `horstoeko/invoicesuite` | v0.0.27 |
| PHP | 8.2.33 (`php:8.2-fpm`, Debian 13 "trixie") |
| Java | OpenJDK 21.0.12.1 (`default-jre-headless`, Debian trixie) |
| KoSIT validator (JAR) | 1.6.2 |
| KoSIT default config (step 2) | "Validator Configuration XRechnung 3.0.2", release 2026-01-31 (Schematron CEF: 1.3.15) |
| CEF artifacts used (step 3 & 4) | [ConnectingEurope/eInvoicing-EN16931](https://github.com/ConnectingEurope/eInvoicing-EN16931), tag `validation-1.3.16` (2026-04-13), file `cii/xslt/EN16931-CII-validation.xslt`, license EUPL 1.2 |

## Replay from scratch

**Dependencies**:
- docker
- (optional) [just](https://github.com/casey/just)

*With just (the easy way)*

```bash
git clone https://github.com/adaniloff/horstoeko-invoicesuite-EN16931-demo.git invoicesuite
cd invoicesuite
just install

# step 1 - valid Factur-X invoice, XSD validation
just console app:factur-x:build

# step 2 - default config KoSIT (XRechnung), simple check
just console app:factur-x:kosit-default

# step 3 - KoSIT + EN16931 (CEF 1.3.16 artefacts), on a valid invoice
just console app:factur-x:kosit-en16931

# step 4 - KoSIT + EN16931 (CEF 1.3.16 artefacts), on an invalid invoice (the amount is inconsistent)
just console app:factur-x:build --broken
just console app:factur-x:kosit-en16931 --broken
```

*Without just*

```bash
git clone https://github.com/adaniloff/horstoeko-invoicesuite-EN16931-demo.git invoicesuite
cd invoicesuite

docker compose up -d
docker compose exec php composer install

# step 1 - valid Factur-X invoice, XSD validation
docker compose exec php bin/console app:factur-x:build

# step 2 - default config KoSIT (XRechnung), simple check
docker compose exec php bin/console app:factur-x:kosit-default

# step 3 - KoSIT + EN16931 (CEF 1.3.16 artefacts), on a valid invoice
docker compose exec php bin/console app:factur-x:kosit-en16931

# step 4 - KoSIT + EN16931 (CEF 1.3.16 artefacts), on an invalid invoice (the amount is inconsistent)
docker compose exec php bin/console app:factur-x:build --broken
docker compose exec php bin/console app:factur-x:kosit-en16931 --broken
```

Each `bin/console` regenerates its XML under `var/factur-x/` or its KoSIT working directory under `var/kosit-*` (not version-controlled, `disableCleanup()` active: inspectable after the fact).

## Proof (step 4)

Tested invoice: `App\FacturX\DemoInvoiceFactory::build(withArithmeticError: true)`. The inconsistency:
the total amount including tax (`GrandTotalAmount`) is increased by €100.00 compared to the sum of the amount excluding tax (1000.00) and the VAT (200.00)
= 1200.00, without any change to the amount excluding tax or the VAT. Nothing in the XML structure is invalid; only
the arithmetic is incorrect.

### 1. XSD validation

```
Facture générée (variante cassée)
---------------------------------

 /var/www/app/var/factur-x/invoice-broken.xml

Validation XSD
--------------

 [OK] Validation XSD : OK (aucun ERROR, aucun INTERNALERROR)
```

### 2. KoSIT validation + isolated EN16931 scenario

```
KoSIT validation (isolated EN16931, broken)
----------------------------------------------------------

 Répertoire de travail : /var/www/app/var/kosit-en16931-broken
 Paquet de scénario : /var/www/app/var/kosit-en16931-broken/en16931-scenario.zip

Messages INFO (sortie du processus Java)
----------------------------------------

 - KoSIT Validator version 1.6.2
 - Loading scenarios from  file:///var/www/app/var/kosit-en16931-broken/kositvalidator-dc1f389e154c57e33ff04e864edd3238/scenarios.xml
 - Using repository  file:///var/www/app/var/kosit-en16931-broken/kositvalidator-dc1f389e154c57e33ff04e864edd3238/
 - 
 - Loaded "EN16931 CII (isole, sans XRechnung) - demo invoicesuite" by Demo invoicesuite from 2026-09-03 
 - The following scenarios are available:
 -   * EN16931 (CII)
 - 
 - 
 - Processing of 1 objects started
 - Processing of 1 objects completed in 258ms
 - Results:
 - --------------------------------------------------------------------------------------------------------------------------------------------------------
 - |File                                                        |Schema |Schematron|Acceptance|Error/Description                                           |
 - |/var/www/app/var/kosit-en16931-broken/kositvalidator-dc1f...|   Y   |    N     |  REJECT  |[BR-CO-15]-Invoice total amount with VAT (BT-112) = Invoice |
 - |e154c57e33ff04e864edd3238/filetovalidate-6a9937556063f-6a993|       |          |          |total amount without VAT (BT-109) + Invoice total VAT amount|
 - |75560641.xml                                                |       |          |          | (BT-110).                                                  |
 - --------------------------------------------------------------------------------------------------------------------------------------------------------
 - Acceptable:  0  Rejected:  1
 - 
 - 
 - ##############################
 - #     Validation failed!     #
 - ##############################
 - 

Messages WARNING
----------------

Messages ERROR
--------------

 - Validation error. One ore more files were rejected
 - Schematron rules for EN16931 (CII) 1.3.16: [BR-CO-15]-Invoice total amount with VAT (BT-112) = Invoice total amount without VAT (BT-109) + Invoice total VAT amount (BT-110).

Messages INTERNALERROR
----------------------

Récapitulatif
-------------

 --------------- -------- 
  Sévérité        Nombre  
 --------------- -------- 
  INFO            25      
  WARNING         0       
  ERROR           2       
  INTERNALERROR   0       
 --------------- -------- 
```

Zero `INTERNALERROR` occurrence on either side.

Reproducibility note: the `filetovalidate-<uniqid>-<uniqid>` fragment in the paths above
changes with each execution (temporary filename generated by the library). However, the verdict
(`Schema`/`Schematron`/`Acceptance`, the `BR-CO-15` code, and severity counts)
remains stable and has been verified through multiple independent runs.
