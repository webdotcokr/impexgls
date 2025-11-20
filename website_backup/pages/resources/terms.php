<?php
require_once '../../config/config.php';
require_once '../../config/meta-config.php';
require_once '../../includes/functions.php';

// 현재 페이지의 메타 정보 가져오기
$current_file = 'pages/resources/incoterms.php';
$page_meta_info = isset($page_meta[$current_file]) ? array_merge($meta_defaults, $page_meta[$current_file]) : $meta_defaults;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php echo generateMetaTags($page_meta_info); ?>
    
    <!-- 폰트 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/custom.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/global.css">
    <!-- 반응형 스타일 -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/responsive.css">
    
    <style>
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <?php
    // 서브페이지 헤더 설정
    $page_header = [
        'category' => 'Resources',
        'title' => 'Terms & Conditions of Service',
        'background' => BASE_URL . '/assets/images/subpage-header-image/Useful Information.webp'
    ];
    include '../../includes/subpage-header.php';
    ?>
    
    <?php
    // 서브 네비게이션 설정
    $subnav_config = [
        'category' => 'Resources',
        'current_page' => 'Knowledge Base',
        'current_url' => $_SERVER['REQUEST_URI'],
        'items' => [
            ['title' => 'Quick Links', 'url' => BASE_URL . '/pages/resources/quick-links.php'],
            ['title' => 'Knowledge Base', 'url' => BASE_URL . '/pages/resources/knowledge-base.php'],
            ['title' => 'Terms & Conditions of Service', 'url' => BASE_URL . '/pages/resources/terms.php']
        ]
    ];
    include '../../includes/mobile-subnav.php';
    ?>

    <!-- Incoterms 콘텐츠 -->
    <section class="py-12 lg:py-20">
        <div class="container">
            <h2 class="text-2xl font-bold">Terms & Conditions of Service</h2>
            <hr class="my-4"/>
            <div class="text-sm">
                <p>These terms and conditions of service constitute a legally binding contract between the "Company" and the "Customer". In the eventthe Company renders services and issues a document containing Terms and Conditions governing such services, the Terms andConditions set forth in such other document(s) shall govern those services.</p>
                <p class="mt-5 mb-2 font-bold">1. Definitions.</p>
                <p>(a) "Company" shall mean <b>IMPEX GLS, INC.</b> , its subsidiaries, related companies, agents and/or representatives;</p>
                <p>(b) "Customer" shall mean the person for which the Company is rendering service, as well as its principals, agents and/orrepresentatives, including, but not limited to, shippers, importers, exporters, carriers, secured parties, warehousemen, buyersand/or sellers, shipper's agents, insurers and underwriters, break-bulk agents, consignees, etc. It is the responsibility of theCustomer to provide notice and copy(s) of these terms and conditions of service to all such agents or representatives;</p>
                <p>(c) "Documentation" shall mean all information received directly or indirectly from Customer, whether in paper or electronic form;</p>
                <p>(d) "Ocean Transportation Intermediaries" ("OTI") shall include an "ocean freight forwarder" and a "non-vessel operating carrier";</p>
                <p>(e) "Third parties" shall include, but not be limited to, the following: "carriers, truckmen, cartmen, lightermen, forwarders, OTIs, customsbrokers, agents, warehousemen and others to which the goods are entrusted for transportation, cartage, handling and/or deliveryand/or storage or otherwise".</p>
                <p class="mt-5 mb-2 font-bold">2. Company as agent.</p>
                <p>The Company acts as the "agent" of the Customer for the purpose of performing duties in connection with the entry and release of goods, post entry services, the securing of export licenses, the filing of export and security documentation on behalf of the Customer and other dealings with Government Agencies, or for arranging for transportation services or other logistics services in any capacity other than as a carrier.</p>
                <p class="mt-5 mb-2 font-bold">3. Limitation of Actions.</p>
                <p?>(a) Unless subject to a specific statute or international convention, all claims against the Company for a potential or actual loss, must be made in writing and received by the Company, within 90 days of the event giving rise to claim; the failure to give the Company timely notice shall be a complete defense to any suit or action commenced by Customer.</p>
                <p>(b) All suits against Company must be filed and properly served on Company as follows:</p>
                <p>(i) For claims arising out of ocean transportation, within ONE year from the date of the loss; (ii) For claims arising out of air transportation, within ONE year from the date of the loss;</p>
                <p>(ii) For claims arising out of air transportation, within ONE year from the date of the loss;</p>
                <p>(iii) For claims arising out of the preparation and/or submission of an import entry(s), within 90 days from the date of liquidation of the entry(s);</p>
                <p>(iv) For any and all other claims of any other type, within ONE year from the date of the loss or damage.</p></p>
                <p class="mt-5 mb-2 font-bold">4. No Liability For The Selection or Services of Third Parties and/or Routes.</p>
                <p>Unless services are performed by persons or firms engaged pursuant to express written instructions from the Customer, Company shall use reasonable care in its selection of third parties, or in selecting the means, route and procedure to be followed in the handling, transportation, clearance and delivery of the shipment; advice by the Company that a particular person or firm has been selected to render services with respect to the goods, shall not be construed to mean that the Company warrants or represents that such person or firm will render such services nor does Company assume responsibility or liability for any actions(s) and/or inactions) of such third parties and/or its agents, and shall not be liable for any delay or loss of any kind, which occurs while a shipment is in the custody or control of a third party or the agent of a third party; all claims in connection with the Act of a third party shall be brought solely against such party and/or its agents; in connection with any such claim, the Company shall reasonably cooperate with the Customer, which shall be liable for any charges or costs incurred by the</p>
                <p class="mt-5 mb-2 font-bold">5. Quotations Not Binding.</p>
                <p>Quotations as to fees, rates of duty, freight charges, insurance premiums or other charges given by the Company to the Customer are for informational purposes only and are subject to change without notice; no quotation shall be binding upon the Company unless the Company in writing agrees to undertake the handling or transportation of the shipment at a specific rate or amount set forth in the quotation and payment arrangements are agreed to between the Company and the Customer.</p>
                <p class="mt-5 mb-2 font-bold">6. Reliance On Information Furnished.</p>
                <p>(a) Customer acknowledges that it is required to review all documents and declarations prepared and/or filed with U.S. Customs & Border Protection, other Government Agency and/or third parties, and will immediately advise the Company of any errors, discrepancies, incorrect statements, or omissions on any declaration or other submission filed on Customers behalf;
(b) In preparing and submitting customs entries, export declarations, applications, security filings, documentation and/or other required data, the Company relies on the correctness of all documentation, whether in written or electronic format, and all information furnished by Customer; Customer shall use reasonable care to ensure the correctness of all such information and shall indemnify and hold the Company harmless from any and all claims asserted and/or liability or losses suffered by reason of the Customer's failure to disclose information or any incorrect, incomplete or false statement by the Customer or its agent, representative or contractor upon which the Company reasonably relied. The Customer agrees that the Customer has an affirmative non-delegable duty to disclose any and all information required to import, export or enter the goods.</p>
                <p class="mt-5 mb-2 font-bold">7. Declaring Higher Value To Third Parties.</p>
                <p>Third parties to whom the goods are entrusted may limit liability for loss or damage; the Company will request excess valuation coverage only upon specific written instructions from the Customer, which must agree to pay any charges therefore; in the absence of written instructions or the refusal of the third party to agree to a higher declared value, at Company's discretion, the goods may be tendered to the third party, subject to the terms of the third party's limitations of liability and/or terms and conditions of service.</p>
                <p class="mt-5 mb-2 font-bold">8. Insurance.</p>
                <p>Unless requested to do so in writing and confirmed to Customer in writing, Company is under no obligation to procureinsurance on Customer's behalf; in all cases, Customer shall pay all premiums and costs in connection with procuring requested insurance.</p>
                <p class="mt-5 mb-2 font-bold">9. Disclaimers; Limitation of Liability.</p>
                <p>(a) Except as specifically set forth herein, Company makes no express or implied warranties in connection with its services;</p>
                <p>(b) In connection with all services performed by the Company, Customer may obtain additional liability coverage, up to the actual ordeclared value of the shipment or transaction, by requesting such coverage and agreeing to make payment therefor, which requestmust be confirmed in writing by the Company prior to rendering services for the covered transaction(s).</p>
                <p>(c) In the absence of additional coverage under (b) above, the Company's liability shall be limited to the following:</p>
                <p class="ml-4">(i) where the claim arises from activities other than those relating to customs business, $0.50/KG per shipment or transaction (max$500), or</p>
                <p class="ml-4">(ii) where the claim arises from activities relating to "Customs business," $50.00 per entry or the amount of brokerage fees paid toCompany for the entry, whichever is less;</p>
                <p>(d) In no event shall Company be liable or responsible for consequential, indirect, incidental, statutory or punitive damages, even if ithas been put on notice of the possibility of such damages, or for the acts of third parties.</p>
                <p class="mt-5 mb-2 font-bold">10. Advancing Money.</p>
                <p>All charges must be paid by Customer in advance unless the Company agrees in writing to extend credit tocustomer; the granting of credit to a Customer in connection with a particular transaction shall not be considered a waiver of thisprovision by the Company.</p>
                <p class="mt-5 mb-2 font-bold">11. Indemnification/Hold Harmless.</p>
                <p>The Customer agrees to indemnify, defend, and hold the Company harmless from any claimsand/or liability, fines, penalties and/or attorneys' fees arising from the importation or exportation of customers merchandise and/or anyconduct of the Customer, including but not limited to the inaccuracy of entry, export or security data supplied by Customer or its agentor representative, which violates any Federal, State and/or other laws, and further agrees to indemnify and hold the Company harmlessagainst any and all liability, loss, damages, costs, claims, penalties, fines and/or expenses, including but not limited to reasonableattorney's fees, which the Company may hereafter incur, suffer or be required to pay by reason of such claims; in the event that any claim,suit or proceeding is brought against the Company, it shall give notice in writing to the Customer by mail at its address on file with theCompany.</p>
                <p class="mt-5 mb-2 font-bold">12. C.O.D. or Cash Collect Shipments.</p>
                <p>Company shall use reasonable care regarding written instructions relating to "Cash/Collect onDeliver (C.O.D.)" shipments, bank drafts, cashier's and/or certified checks, letter(s) of credit and other similar payment documents and/orinstructions regarding collection of monies but shall not have liability if the bank or consignee refuses to pay for the shipment.</p>
                <p class="mt-5 mb-2 font-bold">13. Costs of Collection.</p>
                <p>In any dispute involving monies owed to Company, the Company shall be entitled to all costs of collection,including reasonable attorney's fees and interest at <b>18%</b> per annum or the highest rate allowed by law, whichever is less unless a loweramount is agreed to by Company.</p>
                <p class="mt-5 mb-2 font-bold">14. General Lien and Right To Sell Customer's Property.</p>
                <p>(a) Company shall have a general and continuing lien on any and all property of Customer coming into Company's actual orconstructive possession or control for monies owed to Company with regard to the shipment on which the lien is claimed, a priorshipment(s) and/or both;</p>
                <p>(b) Company shall provide written notice to Customer of its intent to exercise such lien, the exact amount of monies due and owing, aswell as any on-going storage or other charges; Customer shall notify all parties having an interest in its shipment(s) of Company'srights and/or the exercise of such lien</p>
                <p>(c) Unless, within thirty days of receiving notice of lien, Customer posts cash or letter of credit at sight, or, if the amount due is indispute, an acceptable bond equal to 110% of the value of the total amount due, in favor of Company, guaranteeing payment ofthe monies owed, plus all storage charges accrued or to be accrued, Company shall have the right to sell such shipment(s) at publicor private sale or auction and any net proceeds remaining thereafter shall be refunded to Customer.</p>
                <p class="mt-5 mb-2 font-bold">15. No Duty To Maintain Records For Customer.</p>
                <p>Customer acknowledges that pursuant to Sections 508 and 509 of the Tariff Act, asamended, (19 USC §1508 and 1509) it has the duty and is solely liable for maintaining all records required under the Customs and/orother Laws and Regulations of the United States; unless otherwise agreed to in writing, the Company shall only keep such records that itis required to maintain by Statute(s) and/or Regulation(s), but not act as a "recordkeeper" or "recordkeeping agent" for Customer.</p>
                <p class="mt-5 mb-2 font-bold">16. Obtaining Binding Rulings, Filing Protests, etc.</p>
                <p>Unless requested by Customer in writing and agreed to by Company in writing,Company shall be under no obligation to undertake any pre- or post Customs release action, including, but not limited to, obtainingbinding rulings, advising of liquidations, filing of petition(s) and/or protests, etc</p>
                <p class="mt-5 mb-2 font-bold">17. Preparation and Issuance of Bills of Lading.</p>
                <p>Where Company prepares and/or issues a bill of lading, Company shall be under noobligation to specify thereon the number of pieces, packages and/or cartons, etc.; unless specifically requested to do so in writing byCustomer or its agent and Customer agrees to pay for same, Company shall rely upon and use the cargo weight supplied by Customer.</p>
                <p class="mt-5 mb-2 font-bold">18. No Modification or Amendment Unless Written.</p>
                <p>These terms and conditions of service may only be modified, altered or amendedin writing signed by both Customer and Company; any attempt to unilaterally modify, alter or amend same shall be null and void.</p>
                <p class="mt-5 mb-2 font-bold">19. Compensation of Company.</p>
                <p>The compensation of the Company for its services shall be included with and is in addition to the ratesand charges of all carriers and other agencies selected by the Company to transport and deal with the goods and such compensationshall be exclusive of any brokerage, commissions, dividends, or other revenue received by the Company from carriers, insurers and othersin connection with the shipment. On ocean exports, upon request, the Company shall provide a detailed breakout of the components ofall charges assessed and a true copy of each pertinent document relating to these charges. In any referral for collection or action againstthe Customer for monies due the Company, upon recovery by the Company, the Customer shall pay the expenses of collection and/orlitigation, including a reasonable attorney fee.</p>
                <p class="mt-5 mb-2 font-bold">20. Force Majeure.</p>
                <p>Company shall not be liable for losses, damages, delays, wrongful or missed deliveries or nonperformance, in wholeor in part, of its responsibilities under the Agreement, resulting from circumstances beyond the control of either Company or its sub-contractors, including but not limited to: (i) acts of God, including flood, earthquake, storm, hurricane, power failure or other naturaldisaster; (ii) war, hijacking, robbery, theft or terrorist activities; (iii) incidents or deteriorations to means of transportation, (iv) embargoes,(v) civil commotions or riots, (vi) defects, nature or inherent vice of the goods; (vii) acts, breaches of contract or omissions by Customer,Shipper, Consignee or anyone else who may have an interest in the shipment, (viii) acts by any government or any agency or subdivisionthereof, including denial or cancellation of any import/export or other necessary license; or (ix) strikes, lockouts or other labor conflicts.</p>
                <p class="mt-5 mb-2 font-bold">21. Severability.</p>
                <p>In the event any Paragraph(s) and/or portion(s) hereof is found to be invalid and/or unenforceable, then in such eventthe remainder hereof shall remain in Full force and effect. Company's decision to waive any provision herein, either by conduct orotherwise, shall not be deemed to be a further or continuing waiver of such provision or to otherwise waive or invalidate any otherprovision herein.</p>
                <p class="mt-5 mb-2 font-bold">22. Governing Law; Consent to Jurisdiction and Venue.</p>
                <p>These terms and conditions of service and the relationship of the parties shallbe construed according to the laws of the State of Illinois without giving consideration to principles of conflict of law. Customer andCompany (a) irrevocably consent to the jurisdiction of the United States District Court and the State courts of Illinois ; (b) agree that anyaction relating to the services performed by Company, shall only be brought in said courts; (c) consent to the exercise of in personamjurisdiction by said courts over it, and (d) further agree that any action to enforce a judgment may be instituted in any jurisdiction.</p>
            </div>
        </div>
    </section>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
