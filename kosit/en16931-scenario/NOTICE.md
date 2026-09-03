# Provenance and licenses of vendored artifacts

This directory gathers third-party artifacts, each under its own license. None have been
substantively modified (only file paths in `scenarios.xml` have been adjusted for local use).

## `resources/cii/16b/xsd/*.xsd`

UN/CEFACT CII (SCRDM uncoupled) version D16B. Copyright (C) UN/CEFACT (2016). Copying and
distribution permitted without restriction provided the copyright notice is retained - see
the header of each file. Retrieved via the `itplr-kosit/validator-configuration-xrechnung`
package (release XRechnung 3.0.2, 2026-01-31); unmodified.

## `resources/cii/16b/xsl/EN16931-CII-validation.xslt`

EN 16931 Schematron compiled into XSLT 2.0. Copyright ConnectingEurope, licensed under the
**European Union Public Licence (EUPL) v1.2** (notice retained in the file header).

Source: https://github.com/ConnectingEurope/eInvoicing-EN16931
Tag: `validation-1.3.16` (2026-04-13)
Original file: `cii/xslt/EN16931-CII-validation.xslt`

## `resources/default-report.xsl`

KoSIT validation report transformation stylesheet. Sourced from the
[itplr-kosit/validator-configuration-xrechnung](https://github.com/itplr-kosit/validator-configuration-xrechnung)
repository, licensed under the **Apache License, Version 2.0**. The file itself does not
contain a license header in the downloaded release archive; the attribution below
covers this omission.

```
Copyright itplr-kosit (Koordinierungsstelle für IT-Standards)
Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
```

## `scenarios.xml`

Local file, written for this demo, structurally derived from the `EN16931 (CII)` scenario found
in `itplr-kosit/validator-configuration-xrechnung` (Apache-2.0) - see comments at the top
of the file for the details of the derivation.
