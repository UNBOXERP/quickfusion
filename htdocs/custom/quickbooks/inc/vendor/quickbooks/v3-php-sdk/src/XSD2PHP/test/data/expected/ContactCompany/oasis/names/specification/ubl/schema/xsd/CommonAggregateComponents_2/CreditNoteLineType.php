<?php
namespace oasis\names\specification\ubl\schema\xsd\CommonAggregateComponents_2;

/**
 * @xmlNamespace urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2
 * @xmlType
 * @xmlName CreditNoteLineType
 * @var oasis\names\specification\ubl\schema\xsd\CommonAggregateComponents_2\CreditNoteLineType
 * @xmlComponentType ABIE
 * @xmlDictionaryEntryName Credit Note Line. Details
 * @xmlDefinition Information about a Credit Note Line.
 * @xmlObjectClass Credit Note Line
 */
class CreditNoteLineType
{

    
    /**
     * @ComponentType BBIE
     * @DictionaryEntryName Credit Note Line. Identifier
     * @Definition Identifies the Credit Note Line.
     * @Cardinality 1
     * @ObjectClass Credit Note Line
     * @PropertyTerm Identifier
     * @RepresentationTerm Identifier
     * @DataType Identifier. Type
     * @xmlType element
     * @xmlNamespace urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2
     * @xmlMinOccurs 1
     * @xmlMaxOccurs 1
     * @xmlName ID
     * @var oasis\names\specification\ubl\schema\xsd\CommonBasicComponents_2\ID
     */
    public $ID;
    /**
     * @ComponentType BBIE
     * @DictionaryEntryName Credit Note Line. UUID. Identifier
     * @Definition A universally unique identifier for an instance of this ABIE.
     * @Cardinality 0..1
     * @ObjectClass Credit Note Line
     * @PropertyTerm UUID
     * @RepresentationTerm Identifier
     * @DataType Identifier. Type
     * @xmlType element
     * @xmlNamespace urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2
     * @xmlMinOccurs 0
     * @xmlMaxOccurs 1
     * @xmlName UUID
     * @var oasis\names\specification\ubl\schema\xsd\CommonBasicComponents_2\UUID
     */
    public $UUID;
    /**
     * @ComponentType BBIE
     * @DictionaryEntryName Credit Note Line. Note. Text
     * @Definition Free-form text applying to the Credit Note Line. This element may contain notes or any other similar information that is not contained explicitly in another structure.
     * @Cardinality 0..1
     * @ObjectClass Credit Note Line
     * @PropertyTerm Note
     * @RepresentationTerm Text
     * @DataType Text. Type
     * @xmlType element
     * @xmlNamespace urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2
     * @xmlMinOccurs 0
     * @xmlMaxOccurs 1
     * @xmlName Note
     * @var oasis\names\specification\ubl\schema\xsd\CommonBasicComponents_2\Note
     */
    public $Note;
    /**
     * @ComponentType BBIE
     * @DictionaryEntryName Credit Note Line. Credited_ Quantity. Quantity
     * @Definition The quantity of Items credited.
     * @Cardinality 0..1
     * @ObjectClass Credit Note Line
     * @PropertyTermQualifier Credited
     * @PropertyTerm Quantity
     * @RepresentationTerm Quantity
     * @DataType Quantity. Type
     * @xmlType element
     * @xmlNamespace urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2
     * @xmlMinOccurs 0
     * @xmlMaxOccurs 1
     * @xmlName CreditedQuantity
     * @var oasis\names\specification\ubl\schema\xsd\CommonBasicComponents_2\CreditedQuantity
     */
    public $CreditedQuantity;
    /**
     * @ComponentType BBIE
     * @DictionaryEntryName Credit Note Line. Line Extension Amount. Amount
     * @Definition The total amount for the Credit Note Line, including Allowance Charges but net of taxes.
     * @Cardinality 0..1
     * @ObjectClass Credit Note Line
     * @PropertyTerm Line Extension Amount
     * @RepresentationTerm Amount
     * @DataType Amount. Type
     * @xmlType element
     * @xmlNamespace urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2
     * @xmlMinOccurs 0
     * @xmlMaxOccurs 1
     * @xmlName LineExtensionAmount
     * @var oasis\names\specification\ubl\schema\xsd\CommonBasicComponents_2\LineExtensionAmount
     */
    public $LineExtensionAmount;
    /**
     * @ComponentType BBIE
     * @DictionaryEntryName Credit Note Line. Tax Point Date. Date
     * @Definition The date of the Credit Note Line, used to indicate the point at which tax becomes applicable.
     * @Cardinality 0..1
     * @ObjectClass Credit Note Line
     * @PropertyTerm Tax Point Date
     * @RepresentationTerm Date
     * @DataType Date. Type
     * @xmlType element
     * @xmlNamespace urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2
     * @xmlMinOccurs 0
     * @xmlMaxOccurs 1
     * @xmlName TaxPointDate
     * @var oasis\names\specification\ubl\schema\xsd\CommonBasicComponents_2\TaxPointDate
     */
    public $TaxPointDate;
    /**
     * @ComponentType BBIE
     * @DictionaryEntryName Credit Note Line. Accounting Cost Code. Code
     * @Definition The buyer's accounting code applied to the Credit Note Line.
     * @Cardinality 0..1
     * @ObjectClass Credit Note Line
     * @PropertyTerm Accounting Cost Code
     * @RepresentationTerm Code
     * @DataType Code. Type
     * @xmlType element
     * @xmlNamespace urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2
     * @xmlMinOccurs 0
     * @xmlMaxOccurs 1
     * @xmlName AccountingCostCode
     * @var oasis\names\specification\ubl\schema\xsd\CommonBasicComponents_2\AccountingCostCode
     */
    public $AccountingCostCode;
    /**
     * @ComponentType BBIE
     * @DictionaryEntryName Credit Note Line. Accounting Cost. Text
     * @Definition The buyer's accounting code applied to the Credit Note Line, expressed as text.
     * @Cardinality 0..1
     * @ObjectClass Credit Note Line
     * @PropertyTerm Accounting Cost
     * @RepresentationTerm Text
     * @DataType Text. Type
     * @xmlType element
     * @xmlNamespace urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2
     * @xmlMinOccurs 0
     * @xmlMaxOccurs 1
     * @xmlName AccountingCost
     * @var oasis\names\specification\ubl\schema\xsd\CommonBasicComponents_2\AccountingCost
     */
    public $AccountingCost;
    /**
     * @ComponentType ASBIE
     * @DictionaryEntryName Credit Note Line. Discrepancy_ Response. Response
     * @Definition An association to Discrepancy Response; the reason for the Credit.
     * @Cardinality 0..n
     * @ObjectClass Credit Note Line
     * @PropertyTermQualifier Discrepancy
     * @PropertyTerm Response
     * @AssociatedObjectClass Response
     * @xmlType element
     * @xmlNamespace urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2
     * @xmlMinOccurs 0
     * @xmlMaxOccurs unbounded
     * @xmlName DiscrepancyResponse
     * @var oasis\names\specification\ubl\schema\xsd\CommonAggregateComponents_2\DiscrepancyResponse
     */
    public $DiscrepancyResponse;
    /**
     * @ComponentType ASBIE
     * @DictionaryEntryName Credit Note Line. Despatch_ Line Reference. Line Reference
     * @Definition An associative reference to Despatch Line.
     * @Cardinality 0..n
     * @ObjectClass Credit Note Line
     * @PropertyTermQualifier Despatch
     * @PropertyTerm Line Reference
     * @AssociatedObjectClass Line Reference
     * @xmlType element
     * @xmlNamespace urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2
     * @xmlMinOccurs 0
     * @xmlMaxOccurs unbounded
     * @xmlName DespatchLineReference
     * @var oasis\names\specification\ubl\schema\xsd\CommonAggregateComponents_2\DespatchLineReference
     */
    public $DespatchLineReference;
    /**
     * @ComponentType ASBIE
     * @DictionaryEntryName Credit Note Line. Receipt_ Line Reference. Line Reference
     * @Definition An associative reference to Receipt Line.
     * @Cardinality 0..n
     * @ObjectClass Credit Note Line
     * @PropertyTermQualifier Receipt
     * @PropertyTerm Line Reference
     * @AssociatedObjectClass Line Reference
     * @xmlType element
     * @xmlNamespace urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2
     * @xmlMinOccurs 0
     * @xmlMaxOccurs unbounded
     * @xmlName ReceiptLineReference
     * @var oasis\names\specification\ubl\schema\xsd\CommonAggregateComponents_2\ReceiptLineReference
     */
    public $ReceiptLineReference;
    /**
     * @ComponentType ASBIE
     * @DictionaryEntryName Credit Note Line. Billing Reference
     * @Definition An association to Billing Reference.
     * @Cardinality 0..n
     * @ObjectClass Credit Note Line
     * @PropertyTerm Billing Reference
     * @AssociatedObjectClass Billing Reference
     * @xmlType element
     * @xmlNamespace urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2
     * @xmlMinOccurs 0
     * @xmlMaxOccurs unbounded
     * @xmlName BillingReference
     * @var oasis\names\specification\ubl\schema\xsd\CommonAggregateComponents_2\BillingReference
     */
    public $BillingReference;
    /**
     * @ComponentType ASBIE
     * @DictionaryEntryName Credit Note Line. Document Reference
     * @Definition An association to Document Reference.
     * @Cardinality 0..n
     * @ObjectClass Credit Note Line
     * @PropertyTerm Document Reference
     * @AssociatedObjectClass Document Reference
     * @xmlType element
     * @xmlNamespace urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2
     * @xmlMinOccurs 0
     * @xmlMaxOccurs unbounded
     * @xmlName DocumentReference
     * @var oasis\names\specification\ubl\schema\xsd\CommonAggregateComponents_2\DocumentReference
     */
    public $DocumentReference;
    /**
     * @ComponentType ASBIE
     * @DictionaryEntryName Credit Note Line. Pricing Reference
     * @Definition An association to Pricing Reference.
     * @Cardinality 0..1
     * @ObjectClass Credit Note Line
     * @PropertyTerm Pricing Reference
     * @AssociatedObjectClass Pricing Reference
     * @xmlType element
     * @xmlNamespace urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2
     * @xmlMinOccurs 0
     * @xmlMaxOccurs 1
     * @xmlName PricingReference
     * @var oasis\names\specification\ubl\schema\xsd\CommonAggregateComponents_2\PricingReference
     */
    public $PricingReference;
    /**
     * @ComponentType ASBIE
     * @DictionaryEntryName Credit Note Line. Delivery
     * @Definition An association to Delivery.
     * @Cardinality 0..n
     * @ObjectClass Credit Note Line
     * @PropertyTerm Delivery
     * @AssociatedObjectClass Delivery
     * @xmlType element
     * @xmlNamespace urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2
     * @xmlMinOccurs 0
     * @xmlMaxOccurs unbounded
     * @xmlName Delivery
     * @var oasis\names\specification\ubl\schema\xsd\CommonAggregateComponents_2\Delivery
     */
    public $Delivery;
    /**
     * @ComponentType ASBIE
     * @DictionaryEntryName Credit Note Line. Tax Total
     * @Definition An association to Tax Total.
     * @Cardinality 0..n
     * @ObjectClass Credit Note Line
     * @PropertyTerm Tax Total
     * @AssociatedObjectClass Tax Total
     * @xmlType element
     * @xmlNamespace urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2
     * @xmlMinOccurs 0
     * @xmlMaxOccurs unbounded
     * @xmlName TaxTotal
     * @var oasis\names\specification\ubl\schema\xsd\CommonAggregateComponents_2\TaxTotal
     */
    public $TaxTotal;
    /**
     * @ComponentType ASBIE
     * @DictionaryEntryName Credit Note Line. Item
     * @Definition An association to Item
     * @Cardinality 0..1
     * @ObjectClass Credit Note Line
     * @PropertyTerm Item
     * @AssociatedObjectClass Item
     * @xmlType element
     * @xmlNamespace urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2
     * @xmlMinOccurs 0
     * @xmlMaxOccurs 1
     * @xmlName Item
     * @var oasis\names\specification\ubl\schema\xsd\CommonAggregateComponents_2\Item
     */
    public $Item;
    /**
     * @ComponentType ASBIE
     * @DictionaryEntryName Credit Note Line. Price
     * @Definition An association to Price.
     * @Cardinality 0..1
     * @ObjectClass Credit Note Line
     * @PropertyTerm Price
     * @AssociatedObjectClass Price
     * @xmlType element
     * @xmlNamespace urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2
     * @xmlMinOccurs 0
     * @xmlMaxOccurs 1
     * @xmlName Price
     * @var oasis\names\specification\ubl\schema\xsd\CommonAggregateComponents_2\Price
     */
    public $Price;
} // end class CreditNoteLineType
