import { createStore } from 'vuex';
import auth from "@store/modules/auth/index";
import user from "@store/modules/user/index";
import userGroup from "@store/modules/userGroup/index";
import hospital from "@store/modules/hospital/index";
import disease from "@store/modules/disease/index";

import review from "@store/modules/review/index";
import home from "@store/modules/home/index";
import sideBar from "@store/modules/sideBar/index";
import language from "@store/modules/language/index";
import currency from "@store/modules/currency/index";
import notification from "@store/modules/notification/index";
import tax from "@store/modules/tax/index";
import social from "@store/modules/social/index";
import dgd from "@store/modules/dgd/index";
import mediaSetting from "@store/modules/mediaSetting/index";
import media from "@store/modules/media/index";
import shippingZone from "@store/modules/shippingZone/index";
import loading from "@store/modules/loading/index";
import category from "@store/modules/category/index";

import feed from "@store/modules/feed/index";
import menu from "@store/modules/menu/index";
import product from "@store/modules/product/index";
import offer from "@store/modules/offer/index";
import order from "@store/modules/order/index";
import permission from "@store/modules/permission/index";
import translation from "@store/modules/translation/index";
import permalink from "@store/modules/permalink/index";
import upload from "@store/modules/upload/index";
import page from "@store/modules/page/index";
import shippingCountry from "@store/modules/shippingCountry/index";
import vendor from "@store/modules/vendor/index";
import documentSettingsGeneral from "@store/modules/documentSettingsGeneral/index";
import generalSetting from "@store/modules/generalSetting/index";
import documentSetting from "@store/modules/documentSettingIndividual/index";
import emailSetting from "@store/modules/emailSetting/index";
import reminderEmail from "@store/modules/reminderEmail/index";
import postCategory from "@store/modules/postCategory/index";
import post from "@store/modules/post/index";
import shippingLabelSetting from "@store/modules/shippingLabelSetting/index";
import marketplaceSetting from "@store/modules/marketplaceSetting/index";
import marketplaceAuth from "@store/modules/marketplaceAuth/index";
import item from "@store/modules/item/index";
import sharedCart from "@store/modules/sharedCart/index";
import sharedCartStats from "@store/modules/sharedCartStats/index";
import lead from "@store/modules/lead/index";
import leadProject from "@store/modules/leadProject/index.js";
import leadSetting from "@store/modules/leadSetting/index.js";
import loyaltyProgram from "@store/modules/loyaltyProgram/index.js";
import paymentMethod from "@store/modules/paymentMethod/index";
import general from "@store/modules/general/index";
import analytic from "@store/modules/analytic/index";
import customerReports from "@store/modules/customerReports/index";
import tntConsignmentNoteNumber from "@store/modules/tntConsignmentNoteNumber/index";
import dashboard from "@store/modules/dashboard/index";
import revenue from "@store/modules/revenue/index";
import provider from "@store/modules/provider/index.js";
import mySharedCart from "@store/modules/mySharedCart/index.js";
import customerGroup from "@store/modules/customerGroups/index.js";
import offerStats from "@store/modules/offerStats/index.js";
import myOffer from "@store/modules/myOffer/index.js";
import customer from "@store/modules/customer/index.js";
import customerSegment from "@store/modules/customerSegment/index.js";
import emailAds from "@store/modules/emailAds/index.js";
import memberGroup from "@store/modules/memberGroup/index.js";
import member from "@store/modules/member/index.js";
import program from "@store/modules/program/index.js";
import databaseRestore from "@store/modules/databaseRestore/index";
import newsletterReport from "@store/modules/newsletterReport/index";
import shippingCostsUploader from "@store/modules/shippingCostsUploader/index";
import smsHistory from "@store/modules/smsHistory/index";
import smsShablon from "@store/modules/smsShablon/index";
import doctorsFinal from "@store/modules/doctorsFinal/index.js";
import extendedPrice from "@store/modules/extendedPrice/index.js";
import clinic from "@store/modules/clinic/index.js";
import smsBaza from "@store/modules/smsBaza/index.js";
import outgoing from "@store/modules/outgoing/index.js";
import subscribe from "@store/modules/subscribe/index.js";
import recommendation from "@store/modules/recommendation/index.js";
import incoming from "@store/modules/incoming/index.js";
import hospitalsBase from "@store/modules/hospitalsBase/index.js";
import trash from "@store/modules/trash/index.js";
import note from "@store/modules/note/index.js";
import shopFrontend from "@store/modules/shopFrontend/index.js";

const store = createStore({
    modules: {
        auth,
        userGroup,
        hospital,
        disease,

        sideBar,
        language,
        currency,
        customerGroup,
        customer,
        notification,
        loading,
        tax,
        home,
        shippingZone,
        shippingLabelSetting,
        marketplaceSetting,
        marketplaceAuth,
        emailAds,
        social,
        dgd,
        newsletterReport,
        mySharedCart,
        mediaSetting,
        media,
        customerSegment,
        category,
        myOffer,
        user,
        review,
        program,
        memberGroup,
        feed,
        member,
        menu,
        product,
        offer,
        permission,
        dashboard,
        translation,
        permalink,
        documentSettingsGeneral,
        generalSetting,
        documentSetting,
        emailSetting,
        reminderEmail,
        upload,
        page,
        shippingCountry,
        postCategory,
        paymentMethod,
        vendor,
        post,
        revenue,
        item,
        order,
        general,
        sharedCartStats,
        lead,
        leadProject,
        leadSetting,
        loyaltyProgram,
        analytic,
        sharedCart,
        customerReports,
        tntConsignmentNoteNumber,
        offerStats,
        provider,
        databaseRestore,
        shippingCostsUploader,
        smsHistory,
        smsShablon,
        doctorsFinal,
        extendedPrice,
        clinic,
        smsBaza,
        outgoing,
        subscribe,
        recommendation,
        incoming,
        hospitalsBase,
        trash,
        note,
        shopFrontend,
    }
});
export default store;
