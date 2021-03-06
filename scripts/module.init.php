<?php

$modulesPath          = dirname(__FILE__) .'/modules/';

$MODULES_LIST = array(
    'vMain'                         => $modulesPath . 'vMain.php',
    'eMain'                         => $modulesPath . 'eMain.php',
    
    'eFacebookCall'                 => $modulesPath . 'eFacebookCall.php',
    'eGoogleCall'                   => $modulesPath . 'eGoogleCall.php',
    'eTwitterCall'                  => $modulesPath . 'eTwitterCall.php',
    
    'ePaymentGatewayInit'           => $modulesPath . 'ePaymentGatewayInit.php',
    'ePaymentGatewayFinish'         => $modulesPath . 'ePaymentGatewayFinish.php',
    'eComgateStatus'                => $modulesPath . 'eComgateStatus.php',
    'vDeminimisGateway'             => $modulesPath . 'vDeminimisGateway.php',
    'eDeminimisGateway'             => $modulesPath . 'eDeminimisGateway.php',

    'eCheckLogin'                   => $modulesPath . 'user/eCheckLogin.php',
    'vLogin'                        => $modulesPath . 'user/vLogin.php',
    'eLogin'                        => $modulesPath . 'user/eLogin.php',
    'eReLogin'                      => $modulesPath . 'user/eLogin.php',
    'eFacebookLogin'                => $modulesPath . 'user/eLogin.php',
    'eGoogleLogin'                  => $modulesPath . 'user/eLogin.php',
    'eTwitterLogin'                 => $modulesPath . 'user/eLogin.php',
    'eLogout'                       => $modulesPath . 'user/eLogout.php',
    'eChangeProvider'               => $modulesPath . 'user/eChangeProvider.php',
    'eChangeCenter'                 => $modulesPath . 'user/eChangeCenter.php',

    'ePasswordEdit'                 => $modulesPath . 'user/ePasswordEdit.php',
    'vPasswordEdit'                 => $modulesPath . 'user/vPasswordEdit.php',
    'ePasswordChange'               => $modulesPath . 'user/ePasswordChange.php',

    'vUser'                         => $modulesPath . 'user/vUser.php',
    'vUserEdit'                     => $modulesPath . 'user/vUserEdit.php',
    'vUserSubaccountEdit'           => $modulesPath . 'user/vUserSubaccountEdit.php',
    'eUserEdit'                     => $modulesPath . 'user/eUserEdit.php',
    'eUserSave'                     => $modulesPath . 'user/eUserSave.php',
    'eUserDisable'                  => $modulesPath . 'user/eUserDisable.php',
    'eUserDelete'                   => $modulesPath . 'user/eUserDelete.php',
    'eUserEnable'                   => $modulesPath . 'user/eUserEnable.php',
    'eUserSearch'                   => $modulesPath . 'user/eUserSearch.php',
    'eUserValidate'                 => $modulesPath . 'user/eUserValidate.php',
    'eUserSendPassword'             => $modulesPath . 'user/eUserSendPassword.php',
    'eUserCredit'                   => $modulesPath . 'user/eUserCredit.php',
    'vUserCredit'                   => $modulesPath . 'user/vUserCredit.php',
    'vUserCreditHistory'            => $modulesPath . 'user/vUserCreditHistory.php',
    'eUserCreditSave'               => $modulesPath . 'user/eUserCreditSave.php',
    'eUserCreditRefund'             => $modulesPath . 'user/eUserCreditRefund.php',
    'eUserTicketSave'               => $modulesPath . 'user/eUserTicketSave.php',
    'eUserTicketRefund'             => $modulesPath . 'user/eUserTicketRefund.php',
    'eUserProfile'                  => $modulesPath . 'user/eUserProfile.php',
    'eUserRegistrationDelete'       => $modulesPath . 'user/eUserRegistrationDelete.php',
    'eUserFacebookAssign'           => $modulesPath . 'user/eUserFacebookAssign.php',
    'eUserGoogleAssign'             => $modulesPath . 'user/eUserGoogleAssign.php',
    'eUserTwitterAssign'            => $modulesPath . 'user/eUserTwitterAssign.php',
    'vUserPrepaymentInvoice'        => $modulesPath . 'user/vUserPrepaymentInvoice.php',
    'eUserDocumentDelete'           => $modulesPath . 'user/eUserDocumentDelete.php',
    
    'eSettings'                     => $modulesPath . 'settings/eSettings.php',
    'vSettings'                     => $modulesPath . 'settings/vSettings.php',
    'vSettingsEdit'                 => $modulesPath . 'settings/vSettingsEdit.php',
    'eSettingsGeneralSave'          => $modulesPath . 'settings/eSettingsGeneralSave.php',
    'eSettingsGeneralBack'          => $modulesPath . 'settings/eSettingsGeneralBack.php',

    'vInvoice'                      => $modulesPath . 'settings/vInvoice.php',

    'vAvailProfile'                 => $modulesPath . 'settings/vAvailProfile.php',
    'vAvailProfileEdit'             => $modulesPath . 'settings/vAvailProfileEdit.php',
    'eAvailProfileEdit'             => $modulesPath . 'settings/eAvailProfileEdit.php',
    'eAvailProfileSave'             => $modulesPath . 'settings/eAvailProfileSave.php',
    'eAvailProfileCopy'             => $modulesPath . 'settings/eAvailProfileCopy.php',
    'eAvailProfileDelete'           => $modulesPath . 'settings/eAvailProfileDelete.php',
    
    'vAvailExProfile'               => $modulesPath . 'settings/vAvailExProfile.php',
    'vAvailExProfileEdit'           => $modulesPath . 'settings/vAvailExProfileEdit.php',
    'eAvailExProfileEdit'           => $modulesPath . 'settings/eAvailExProfileEdit.php',
    'eAvailExProfileSave'           => $modulesPath . 'settings/eAvailExProfileSave.php',
    'eAvailExProfileCopy'           => $modulesPath . 'settings/eAvailExProfileCopy.php',
    'eAvailExProfileDelete'         => $modulesPath . 'settings/eAvailExProfileDelete.php',
    
    'vUnitProfile'                  => $modulesPath . 'settings/vUnitProfile.php',
    'vUnitProfileEdit'              => $modulesPath . 'settings/vUnitProfileEdit.php',
    'eUnitProfileEdit'              => $modulesPath . 'settings/eUnitProfileEdit.php',
    'eUnitProfileSave'              => $modulesPath . 'settings/eUnitProfileSave.php',
    'eUnitProfileCopy'              => $modulesPath . 'settings/eUnitProfileCopy.php',
    'eUnitProfileDelete'            => $modulesPath . 'settings/eUnitProfileDelete.php',
    
    'vTag'                          => $modulesPath . 'settings/vTag.php',
    'vTagEdit'                      => $modulesPath . 'settings/vTagEdit.php',
    'eTagEdit'                      => $modulesPath . 'settings/eTagEdit.php',
    'eTagSave'                      => $modulesPath . 'settings/eTagSave.php',
    'eTagCopy'                      => $modulesPath . 'settings/eTagCopy.php',
    'eTagDelete'                    => $modulesPath . 'settings/eTagDelete.php',
    'eTagPrepareCommodityCopy'      => $modulesPath . 'settings/eTagPrepareCommodityCopy.php',
    'eTagCommodityCopy'             => $modulesPath . 'settings/eTagCommodityCopy.php',
    'eTagCommodityDelete'           => $modulesPath . 'settings/eTagCommodityDelete.php',
    
    'vPriceList'                    => $modulesPath . 'settings/vPriceList.php',
    'vPriceListEdit'                => $modulesPath . 'settings/vPriceListEdit.php',
    'ePriceListEdit'                => $modulesPath . 'settings/ePriceListEdit.php',
    'ePriceListSave'                => $modulesPath . 'settings/ePriceListSave.php',
    'ePriceListCopy'                => $modulesPath . 'settings/ePriceListCopy.php',
    'ePriceListDelete'              => $modulesPath . 'settings/ePriceListDelete.php',
    
    'vProviderAccountType'          => $modulesPath . 'settings/vProviderAccountType.php',
    'vProviderAccountTypeEdit'      => $modulesPath . 'settings/vProviderAccountTypeEdit.php',
    'eProviderAccountTypeEdit'      => $modulesPath . 'settings/eProviderAccountTypeEdit.php',
    'eProviderAccountTypeSave'      => $modulesPath . 'settings/eProviderAccountTypeSave.php',
    'eProviderAccountTypeCopy'      => $modulesPath . 'settings/eProviderAccountTypeCopy.php',
    'eProviderAccountTypeDelete'    => $modulesPath . 'settings/eProviderAccountTypeDelete.php',

    'eProviderTextStorageInit'      => $modulesPath . 'settings/eProviderTextStorageInit.php',
    'eProviderTextStorageAdd'       => $modulesPath . 'settings/eProviderTextStorageAdd.php',
    'eProviderTextStorageDelete'    => $modulesPath . 'settings/eProviderTextStorageDelete.php',
    
    'vTicketEdit'                   => $modulesPath . 'settings/vTicketEdit.php',
    'eTicketEdit'                   => $modulesPath . 'settings/eTicketEdit.php',
    'eTicketSave'                   => $modulesPath . 'settings/eTicketSave.php',
    'eTicketCopy'                   => $modulesPath . 'settings/eTicketCopy.php',
    'eTicketDelete'                 => $modulesPath . 'settings/eTicketDelete.php',

    'vVoucherEdit'                  => $modulesPath . 'settings/vVoucherEdit.php',
    'eVoucherEdit'                  => $modulesPath . 'settings/eVoucherEdit.php',
    'eVoucherSave'                  => $modulesPath . 'settings/eVoucherSave.php',
    'eVoucherCopy'                  => $modulesPath . 'settings/eVoucherCopy.php',
    'eVoucherDelete'                => $modulesPath . 'settings/eVoucherDelete.php',
    
    'vReservationCondition'         => $modulesPath . 'settings/vReservationCondition.php',
    'vReservationConditionEdit'     => $modulesPath . 'settings/vReservationConditionEdit.php',
    'eReservationConditionEdit'     => $modulesPath . 'settings/eReservationConditionEdit.php',
    'eReservationConditionSave'     => $modulesPath . 'settings/eReservationConditionSave.php',
    'eReservationConditionCopy'     => $modulesPath . 'settings/eReservationConditionCopy.php',
    'eReservationConditionDelete'   => $modulesPath . 'settings/eReservationConditionDelete.php',
    
    'vNotificationTemplate'         => $modulesPath . 'settings/vNotificationTemplate.php',
    'vNotificationTemplateEdit'     => $modulesPath . 'settings/vNotificationTemplateEdit.php',
    'eNotificationTemplateEdit'     => $modulesPath . 'settings/eNotificationTemplateEdit.php',
    'eNotificationTemplateSave'     => $modulesPath . 'settings/eNotificationTemplateSave.php',
    'eNotificationTemplateCopy'     => $modulesPath . 'settings/eNotificationTemplateCopy.php',
    'eNotificationTemplateDelete'   => $modulesPath . 'settings/eNotificationTemplateDelete.php',

    'vDocumentTemplate'             => $modulesPath . 'settings/vDocumentTemplate.php',
    'vDocumentTemplateEdit'         => $modulesPath . 'settings/vDocumentTemplateEdit.php',
    'eDocumentTemplateEdit'         => $modulesPath . 'settings/eDocumentTemplateEdit.php',
    'eDocumentTemplateSave'         => $modulesPath . 'settings/eDocumentTemplateSave.php',
    'eDocumentTemplateCopy'         => $modulesPath . 'settings/eDocumentTemplateCopy.php',
    'eDocumentTemplateDelete'       => $modulesPath . 'settings/eDocumentTemplateDelete.php',
    
    'eReport'                       => $modulesPath . 'report/eReport.php',
    'vReport'                       => $modulesPath . 'report/vReport.php',
    'vReportExport'                 => $modulesPath . 'report/vReportExport.php',
    'eReportClear'                  => $modulesPath . 'report/eReportClear.php',
    'eReportSort'                   => $modulesPath . 'report/eReportSort.php',
    'eReportSearchReservation'      => $modulesPath . 'report/eReportSearchReservation.php',
    'eReportSearchCredit'           => $modulesPath . 'report/eReportSearchCredit.php',
    'eReportSearchAttendee'         => $modulesPath . 'report/eReportSearchAttendee.php',
    'eReportSearchUser'             => $modulesPath . 'report/eReportSearchUser.php',
    
    'vCustomer'                     => $modulesPath . 'customer/vCustomer.php',
    'vCustomerEdit'                 => $modulesPath . 'customer/vCustomerEdit.php',
    'eCustomerEdit'                 => $modulesPath . 'customer/eCustomerEdit.php',
    'eMyCustomerEdit'               => $modulesPath . 'customer/eMyCustomerEdit.php',
    'eCustomerSave'                 => $modulesPath . 'customer/eCustomerSave.php',
    'eCustomerDelete'               => $modulesPath . 'customer/eCustomerDelete.php',
    'vCustomerCredit'               => $modulesPath . 'customer/vCustomerCredit.php',
    'vCustomerCreditHistory'        => $modulesPath . 'customer/vCustomerCreditHistory.php',
    'eCustomerCredit'               => $modulesPath . 'customer/eCustomerCredit.php',
    'eCustomerSearch'               => $modulesPath . 'customer/eCustomerSearch.php',
    
    'vEvent'                        => $modulesPath . 'commodity/vEvent.php',
    'vEventEdit'                    => $modulesPath . 'commodity/vEventEdit.php',
    'eEventEdit'                    => $modulesPath . 'commodity/eEventEdit.php',
    'eEventCycleCreate'             => $modulesPath . 'commodity/eEventCycleCreate.php',
    'eEventCycleItems'              => $modulesPath . 'commodity/eEventCycleItems.php',
    'eEventSave'                    => $modulesPath . 'commodity/eEventSave.php',
    'vEventGroupCreate'             => $modulesPath . 'commodity/vEventGroupCreate.php',
    'eEventGroupCreate'             => $modulesPath . 'commodity/eEventGroupCreate.php',
    'eEventGroupSave'               => $modulesPath . 'commodity/eEventGroupSave.php',
    'eEventCopy'                    => $modulesPath . 'commodity/eEventCopy.php',
    'eEventDelete'                  => $modulesPath . 'commodity/eEventDelete.php',
    'eEventGroupDelete'             => $modulesPath . 'commodity/eEventGroupDelete.php',
    'eEventGroupDisable'            => $modulesPath . 'commodity/eEventGroupDisable.php',
    'eEventGroupEdit'               => $modulesPath . 'commodity/eEventGroupEdit.php',
    'eEventReservation'             => $modulesPath . 'commodity/eEventReservation.php',
    'vEventCalendar'                => $modulesPath . 'commodity/vEventCalendar.php',
    'vEventSubstituteEdit'          => $modulesPath . 'commodity/vEventSubstituteEdit.php',
    'eEventSubstituteEdit'          => $modulesPath . 'commodity/eEventSubstituteEdit.php',
    'eEventSubstituteSave'          => $modulesPath . 'commodity/eEventSubstituteSave.php',
    'eEventSubstituteReservation'   => $modulesPath . 'commodity/eEventSubstituteReservation.php',
    'eEventSubstituteDelete'        => $modulesPath . 'commodity/eEventSubstituteDelete.php',
    'vEventUserExport'              => $modulesPath . 'commodity/vEventUserExport.php',
    'vEventSummary'                 => $modulesPath . 'commodity/vEventSummary.php',
    'vEventBadge'                   => $modulesPath . 'commodity/vEventBadge.php',
    
    'vResource'                     => $modulesPath . 'commodity/vResource.php',
    'vResourceEdit'                 => $modulesPath . 'commodity/vResourceEdit.php',
    'eResourceEdit'                 => $modulesPath . 'commodity/eResourceEdit.php',
    'eResourceSave'                 => $modulesPath . 'commodity/eResourceSave.php',
    'eResourceCopy'                 => $modulesPath . 'commodity/eResourceCopy.php',
    'eResourceDelete'               => $modulesPath . 'commodity/eResourceDelete.php',
    'eResourceGroupDelete'          => $modulesPath . 'commodity/eResourceGroupDelete.php',
    'eResourceGroupDisable'         => $modulesPath . 'commodity/eResourceGroupDisable.php',
    'eResourceGroupEdit'            => $modulesPath . 'commodity/eResourceGroupEdit.php',
    'eResourceGroupSave'            => $modulesPath . 'commodity/eResourceGroupSave.php',
    'eResourceReservation'          => $modulesPath . 'commodity/eResourceReservation.php',
    'vResourceReservation'          => $modulesPath . 'commodity/vResourceReservation.php',
    'vResourceCalendar'             => $modulesPath . 'commodity/vResourceCalendar.php',
    
    'vResourcePool'                 => $modulesPath . 'commodity/vResourcePool.php',
    'vResourcePoolEdit'             => $modulesPath . 'commodity/vResourcePoolEdit.php',
    'eResourcePoolEdit'             => $modulesPath . 'commodity/eResourcePoolEdit.php',
    'eResourcePoolSave'             => $modulesPath . 'commodity/eResourcePoolSave.php',
    'eResourcePoolDelete'           => $modulesPath . 'commodity/eResourcePoolDelete.php',
    'eResourcePoolGroupDelete'      => $modulesPath . 'commodity/eResourcePoolGroupDelete.php',
    'eResourcePoolGroupDisable'     => $modulesPath . 'commodity/eResourcePoolGroupDisable.php',
    'vResourcePoolResourceSelect'   => $modulesPath . 'commodity/vResourcePoolResourceSelect.php',
    'eResourcePoolResourceSelect'   => $modulesPath . 'commodity/eResourcePoolResourceSelect.php',
    
    'vReservation'                        => $modulesPath . 'reservation/vReservation.php',
    'vReservationEdit'                    => $modulesPath . 'reservation/vReservationEdit.php',
    'eReservationEdit'                    => $modulesPath . 'reservation/eReservationEdit.php',
    'eReservationSave'                    => $modulesPath . 'reservation/eReservationSave.php',
    'eReservationCopy'                    => $modulesPath . 'reservation/eReservationCopy.php',
    'eReservationDelete'                  => $modulesPath . 'reservation/eReservationDelete.php',
    'eReservationChooseCancel'            => $modulesPath . 'reservation/eReservationChooseCancel.php',
    'eReservationPrepareCancel'           => $modulesPath . 'reservation/eReservationPrepareCancel.php',
    'eReservationCancel'                  => $modulesPath . 'reservation/eReservationCancel.php',
    'eReservationCancelEventPackItem'     => $modulesPath . 'reservation/eReservationCancelEventPackItem.php',
    'eReservationChooseFail'              => $modulesPath . 'reservation/eReservationChooseFail.php',
    'eReservationFail'                    => $modulesPath . 'reservation/eReservationFail.php',
    'eReservationFailEventPackItem'       => $modulesPath . 'reservation/eReservationFailEventPackItem.php',
    'eReservationPreparePay'              => $modulesPath . 'reservation/eReservationPreparePay.php',
    'eReservationPrepareRefund'           => $modulesPath . 'reservation/eReservationPrepareRefund.php',
    'eReservationCancelRefund'            => $modulesPath . 'reservation/eReservationCancelRefund.php',
    'vReservationTicket'                  => $modulesPath . 'reservation/vReservationTicket.php',
    'vReservationTicketForm'              => $modulesPath . 'reservation/vReservationTicketForm.php',
    'eReservationTicketForm'              => $modulesPath . 'reservation/eReservationTicketForm.php',
    'vReservationBadge'                   => $modulesPath . 'reservation/vReservationBadge.php',
    'vReservationReceipt'                 => $modulesPath . 'reservation/vReservationReceipt.php',
    'vReservationInvoice'                 => $modulesPath . 'reservation/vReservationInvoice.php',
    'eReservationGroupDelete'             => $modulesPath . 'reservation/eReservationGroupDelete.php',
    
    'eXmlRequest'                         => $modulesPath . 'xml/eXmlRequest.php',
    'eAjaxRequest'                        => $modulesPath . 'ajax/eAjaxRequest.php',
    
    /* provider portal */
    'vPageTemplate'                       => $modulesPath . 'providerportal/vPageTemplate.php',
    'vPageTemplateEdit'                   => $modulesPath . 'providerportal/vPageTemplateEdit.php',
    'ePageTemplateEdit'                   => $modulesPath . 'providerportal/ePageTemplateEdit.php',
    'ePageTemplateSave'                   => $modulesPath . 'providerportal/ePageTemplateSave.php',
    'ePageTemplateCopy'                   => $modulesPath . 'providerportal/ePageTemplateCopy.php',
    'ePageTemplateDelete'                 => $modulesPath . 'providerportal/ePageTemplateDelete.php',
    'vPortalTemplate'                     => $modulesPath . 'providerportal/vPortalTemplate.php',
    'vPortalTemplateEdit'                 => $modulesPath . 'providerportal/vPortalTemplateEdit.php',
    'ePortalTemplateEdit'                 => $modulesPath . 'providerportal/ePortalTemplateEdit.php',
    'ePortalTemplateSave'                 => $modulesPath . 'providerportal/ePortalTemplateSave.php',
    'ePortalTemplateCopy'                 => $modulesPath . 'providerportal/ePortalTemplateCopy.php',
    'ePortalTemplateDelete'               => $modulesPath . 'providerportal/ePortalTemplateDelete.php',
    'vProviderPortal'                     => $modulesPath . 'providerportal/vProviderPortal.php',
    'eProviderPortalPrepare'              => $modulesPath . 'providerportal/eProviderPortalPrepare.php',
    'vProviderPortalPrepare'              => $modulesPath . 'providerportal/vProviderPortalPrepare.php',
    'eProviderPortalCreate'               => $modulesPath . 'providerportal/eProviderPortalCreate.php',
    'vProviderPortalEdit'                 => $modulesPath . 'providerportal/vProviderPortalEdit.php',
    'eProviderPortalEdit'                 => $modulesPath . 'providerportal/eProviderPortalEdit.php',
    'eProviderPortalSave'                 => $modulesPath . 'providerportal/eProviderPortalSave.php',
    'eProviderPortalCopy'                 => $modulesPath . 'providerportal/eProviderPortalCopy.php',
    'eProviderPortalDelete'               => $modulesPath . 'providerportal/eProviderPortalDelete.php',
    'eProviderPortalPageCopy'             => $modulesPath . 'providerportal/eProviderPortalPageCopy.php',
    'eProviderPortalPageDelete'           => $modulesPath . 'providerportal/eProviderPortalPageDelete.php',
    'vProviderPortalPageEdit'             => $modulesPath . 'providerportal/vProviderPortalPageEdit.php',
    'eProviderPortalPageEdit'             => $modulesPath . 'providerportal/eProviderPortalPageEdit.php',
    'eProviderPortalPageSave'             => $modulesPath . 'providerportal/eProviderPortalPageSave.php',
    'eProviderPortalPagePrepare'          => $modulesPath . 'providerportal/eProviderPortalPagePrepare.php',
    'vProviderPortalPagePrepare'          => $modulesPath . 'providerportal/vProviderPortalPagePrepare.php',
    'eProviderPortalPageCreate'           => $modulesPath . 'providerportal/eProviderPortalPageCreate.php',
    'vProviderPortalView'                 => $modulesPath . 'providerportal/vProviderPortalView.php',
    
    'eInPageInit'                   => $modulesPath . 'inpage/eInit.php',
    'vInPage'                       => $modulesPath . 'inpage/vInPage.php',
    'eInPageLogin'                  => $modulesPath . 'inpage/eLogin.php',
    'eInPageLogout'                 => $modulesPath . 'inpage/eLogout.php',
    'vInPageEvent'                  => $modulesPath . 'inpage/vEvent.php',
    'eInPageEventReserve'           => $modulesPath . 'inpage/eEventReserve.php',
    'eInPageResourceReserve'        => $modulesPath . 'inpage/eResourceReserve.php',
    'vInPageResource'               => $modulesPath . 'inpage/vResource.php',
    'vInPageReservation'            => $modulesPath . 'inpage/vReservation.php',
    'eInPageReservationCancel'      => $modulesPath . 'inpage/eReservationCancel.php',
    'eInPageReservationPay'         => $modulesPath . 'inpage/eReservationPay.php',
    'eInPageRegistration'           => $modulesPath . 'inpage/eRegistration.php',
    'eInPageRegistrationSave'       => $modulesPath . 'inpage/eRegistrationSave.php',
    'vInPageRegistration'           => $modulesPath . 'inpage/vRegistration.php',
    'eInPageConfirmRegistration'    => $modulesPath . 'inpage/eRegistrationConfirm.php',
    
    'eInPageCzechTourismResource'         => $modulesPath . 'inpage/eCzechTourismResource.php',
    'vInPageCzechTourismResource'         => $modulesPath . 'inpage/vCzechTourismResource.php',
    'vInPageCzechTourismResourceDetail'   => $modulesPath . 'inpage/vCzechTourismResourceDetail.php',
    'vInPageCzechTourismEvent'            => $modulesPath . 'inpage/vCzechTourismEvent.php',
    'eInPageCzechTourismEventReserve'     => $modulesPath . 'inpage/eCzechTourismEventReserve.php',
    'vInPageCzechTourismReservation'      => $modulesPath . 'inpage/vCzechTourismReservation.php',
    'vCzechTourismPortal'                 => $modulesPath . 'provider_portal/vCzechTourismPortal.php',
    'eCzechTourismPortalOrder'            => $modulesPath . 'provider_portal/eCzechTourismPortalOrder.php',
    'eCzechTourismPortalOrderSave'        => $modulesPath . 'provider_portal/eCzechTourismPortalOrderSave.php',
    'eCzechTourismPortalEventReserve'     => $modulesPath . 'provider_portal/eCzechTourismPortalEventReserve.php',
    );

?>
