<?php

declare(strict_types=1);

namespace App\FacturX;

use horstoeko\invoicesuite\codelists\InvoiceSuiteCodelistCountryCodes;
use horstoeko\invoicesuite\codelists\InvoiceSuiteCodelistCurrencyCodes;
use horstoeko\invoicesuite\codelists\InvoiceSuiteCodelistDocumentTypes;
use horstoeko\invoicesuite\codelists\InvoiceSuiteCodelistDutyTaxFeeCategories;
use horstoeko\invoicesuite\codelists\InvoiceSuiteCodelistSchemeIdentifiers;
use horstoeko\invoicesuite\codelists\InvoiceSuiteCodelistUnitCodes;
use horstoeko\invoicesuite\codelists\InvoiceSuiteCodelistVatTypeCodes;
use horstoeko\invoicesuite\InvoiceSuiteDocumentBuilder;

/**
 * Provider zffxcomfort = urn:cen.eu:en16931:2017 (see InvoiceSuiteZfFxComfortProvider::getParameters()).
 */
final class DemoInvoiceFactory
{
    private const PROVIDER_ID = 'zffxcomfort';

    private const VAT_RATE = 20.0;
    private const LINE_1_NET = 500.0;
    private const LINE_2_NET = 500.0;
    private const NET_TOTAL = self::LINE_1_NET + self::LINE_2_NET;
    private const TAX_TOTAL = self::NET_TOTAL * self::VAT_RATE / 100;
    private const GROSS_TOTAL = self::NET_TOTAL + self::TAX_TOTAL;

    public static function build(bool $withArithmeticError = false): InvoiceSuiteDocumentBuilder
    {
        $grossTotal = $withArithmeticError ? self::GROSS_TOTAL + 100.0 : self::GROSS_TOTAL;

        $builder = InvoiceSuiteDocumentBuilder::createByProviderUniqueId(self::PROVIDER_ID);

        $builder
            ->setDocumentNo($withArithmeticError ? 'FX-2026-0002' : 'FX-2026-0001')
            ->setDocumentType(InvoiceSuiteCodelistDocumentTypes::COMMERCIAL_INVOICE->value)
            ->setDocumentDate(new \DateTimeImmutable('2026-09-03'))
            ->setDocumentCurrency(InvoiceSuiteCodelistCurrencyCodes::EURO->value)
            ->setDocumentBuyerReference('PO-2026-001')
            ->setDocumentSellerName('Société Démo SARL')
            ->setDocumentSellerAddress('1 rue de la Paix', null, null, '75002', 'Paris', InvoiceSuiteCodelistCountryCodes::FRANKREICH->value)
            ->setDocumentSellerLegalOrganisation(InvoiceSuiteCodelistSchemeIdentifiers::SYST_INFO_ET_REPE_DES_ENTR_ET_DES_ETAB_SIRE->value, '552100554', null)
            ->setDocumentSellerTaxRegistration('VA', 'FR40552100554')
            ->setDocumentSellerContact('Service Facturation', null, null, null, 'facturation@demo-sarl.example')
            ->setDocumentBuyerName('Client Test SA')
            ->setDocumentBuyerAddress('10 avenue des Champs', null, null, '69002', 'Lyon', InvoiceSuiteCodelistCountryCodes::FRANKREICH->value)
            ->setDocumentBuyerTaxRegistration('VA', 'FR76123456789')
            ->setDocumentPaymentMean('58', 'Virement SEPA', null, null, null, 'FR7630006000011234567890189', 'Société Démo SARL', null, 'AGRIFRPP')
            ->setDocumentPaymentTerm('Paiement à 30 jours', new \DateTimeImmutable('2026-10-03'))
            ->addDocumentTax(
                InvoiceSuiteCodelistDutyTaxFeeCategories::STANDARD_RATE->value,
                InvoiceSuiteCodelistVatTypeCodes::VALUE_ADDED_TAX,
                self::NET_TOTAL,
                self::TAX_TOTAL,
                self::VAT_RATE
            )
            ->setDocumentSummation(
                self::NET_TOTAL,
                0.0,
                0.0,
                self::NET_TOTAL,
                self::TAX_TOTAL,
                self::TAX_TOTAL,
                $grossTotal,
                $grossTotal,
                0.0,
                0.0
            );

        $builder
            ->addDocumentPosition('1')
            ->setDocumentPositionProductDetails(null, 'Prestation de conseil')
            ->setDocumentPositionNetPrice(100.0, 1.0)
            ->setDocumentPositionQuantities(5.0, InvoiceSuiteCodelistUnitCodes::REC20_HOUR->value)
            ->setDocumentPositionTax(
                InvoiceSuiteCodelistDutyTaxFeeCategories::STANDARD_RATE->value,
                InvoiceSuiteCodelistVatTypeCodes::VALUE_ADDED_TAX,
                null,
                self::VAT_RATE
            )
            ->setDocumentPositionSummation(self::LINE_1_NET);

        $builder
            ->addDocumentPosition('2')
            ->setDocumentPositionProductDetails(null, 'Licence logicielle annuelle')
            ->setDocumentPositionNetPrice(250.0, 1.0)
            ->setDocumentPositionQuantities(2.0, InvoiceSuiteCodelistUnitCodes::REC20_PIECE->value)
            ->setDocumentPositionTax(
                InvoiceSuiteCodelistDutyTaxFeeCategories::STANDARD_RATE->value,
                InvoiceSuiteCodelistVatTypeCodes::VALUE_ADDED_TAX,
                null,
                self::VAT_RATE
            )
            ->setDocumentPositionSummation(self::LINE_2_NET);

        return $builder;
    }
}
