<script setup>
import {computed} from "vue";
import {useStore} from "vuex";

const props = defineProps({
    form: {
        type: Object,
        default: null,
    },
})
const store = useStore()
const getAdminBaseUrl = computed(() => store.getters['general/getAdminBaseUrl']);

const link = document.createElement('link');
link.rel = 'stylesheet';
link.href = `${getAdminBaseUrl.value}/css/pdf.css`;
document.head.appendChild(link);
</script>

<template>
    <div class="proforma a4-page body ">
        <table class="table-pdf head container">
            <tbody class="tbody">
            <tr class="tr">
                <td class="header td">
                    <img :src="form.media.length ? form.media[0].path : ''" class="img" >
                </td>
                <td class="shop-info td">
                    <h3 class="h3">{{ form.name }}</h3>
                    <div>
                        <span v-html="form.address"></span>
                        {{ form.email }} | Tel.: {{ form.phone }}
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <div class="document-type-label">
            <h1 class="h1">{{ form.document_title}} {invoice_number}</h1>
            <p class="p">The invoice date corresponds to the service date.</p>
        </div>
        <table class="order-data-addresses table-pdf">
            <tbody class="tbody">
            <tr class="tr">
                <td class="address billing-address td">
                    <p class="p">
                        <strong>{{ form.name }}</strong><br>
                        <span v-html="form.seller_info"></span>
                    </p>
                    <hr>
                    <div class="mb-2.5">
                        <div>{customer_info}</div>
                        <div v-if="form.display_phone_number">{customer_phone}</div>
                        <div v-if="form.display_email_address">{customer_email}</div>
                    </div>
                </td>
                <td class="order-data td">
                    <table class="order-info table-pdf">
                        <tbody class="tbody">
                        <tr class="tr">
                            <th class="th">Order Number:</th>
                            <td class="td">189093</td>
                        </tr>
                        <tr class="tr">
                            <th class="th">Order Date:</th>
                            <td class="td">12.09.2024</td>
                        </tr>
                        <tr class="tr">
                            <th class="th">Payment Method:</th>
                            <td class="td">Bank Transfer</td>
                        </tr>
                        </tbody>
                    </table>
                    <br>
                </td>
            </tr>
            </tbody>
        </table>
        <table class="order-details table-pdf">
            <thead class="thead">
            <tr class="tr">
                <th class="th">Product</th>
                <th class="th" style="text-align: right">Quantity</th>
                <th class="th" style="text-align: right">Price</th>
            </tr>
            </thead>
            <tbody class="tbody">
            <tr class="tr product-item my-border">
                <td class="td product">
                    <div class="item-name">Release liner voor Epoxy Giethars</div>
                    <div class="wc-item-meta">
                        <div>
                            <strong class="wc-item-meta-label">Hoeveelheid:</strong> 5 stuks - u bespaart 15 €!
                        </div>
                        <br>
                        <div class="sku">
                            SKU: Z-1036
                        </div>
                    </div>
                </td>
                <td class="td quantity">1</td>
                <td class="td price">34,41 €</td>
            </tr>
            <tr class="tr product-item my-border">
                <td class="td product">
                    <div class="item-name">Epoxy Gietharsen voor riviertafels en gietingen tot 5cm (ECO+/ PRO+)
                    </div>
                    <div class="item-meta">
                        <ul class="wc-item-meta">
                            <li>
                                <strong>
                                    1. Farbe auswählen :
                                </strong> Sand
                            </li>
                            <li>
                                <strong>
                                    2. Struktur auswählen:
                                </strong>
                                Feinkörnig (Schicht 1: mittel + Schicht 2: fein)
                            </li>
                            <li>
                                <strong>
                                    3. Menge (m²) für 2 Schichten:</strong> 63m² (142kg)
                            </li>
                            <li>
                                <strong>
                                    4. PU-Versiegelung mitbestellen?:
                                </strong> Ja, glänzende Versiegelung mitbestellen.
                            </li>
                        </ul>
                    </div>
                    <br>
                    <div class="sku">
                        SKU: Z-1036
                    </div>
                </td>
                <td class="td quantity">1</td>
                <td class="td price">34,41 €</td>
            </tr>
            <tr class="tr product-item">
                <td></td>
                <td colspan="2">
                    <table class="totals table-pdf" style="margin-top: -2px">
                        <tfoot class="tfoot">
                        <tr class="tr">
                            <th class="th description">Net Amount</th>
                            <td class="td price">28.92 EUR</td>
                        </tr>
                        <tr class="tr">
                            <th class="th description">Free shipping</th>
                            <td class="td price">0.00 EUR</td>
                        </tr>
                        <tr class="tr">
                            <th class="th description">Total Net Amount</th>
                            <td class="td price">28.92 EUR</td>
                        </tr>
                        <tr class="tr">
                            <th class="th description">Sales Tax 19%</th>
                            <td class="td price">5.49 EUR</td>
                        </tr>
                        <tr class="tr">
                            <th class="th description">Total Amount</th>
                            <td class="td price">34.41 EUR</td>
                        </tr>
                        </tfoot>
                    </table>
                </td>
            </tr>
            </tbody>
        </table>

        <div id="footer" v-html="form.footer_text"></div>
    </div>
</template>
<!--<style>-->

<!--</style>-->
<style lang="scss" scoped>

.a4-page {
    width: 100%;
    position: relative;
    margin: 0 auto;
    border: 1px solid rgb(226 232 240 / var(--tw-border-opacity));
    padding: 40px;
    height: auto;
}
</style>
