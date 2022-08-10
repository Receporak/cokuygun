globalVue = new Vue({
    delimiters: ["${", "}"],
    data: {},
    methods: {
        /**
         * @param {object} [object = {}]
         * @param {string} [object.commissionRate = 0] - 0 and above
         * @param {string} [object.deposit = 0] - 0 Like 0 or 12.55
         * @param {string} [object.digit = 2] - 0 and above
         * @param {string} [object.type = price] - price | stock | percent | commission - enter one of these
         * @param {string} [object.value = 0] - 0 Like 0 or 12.55
         * @return string
         */
        viewData: function (object) {
            if (object.type === "price"){
                return accounting.formatMoney(parseFloat(object.value).toFixed(parseInt(object.digit)), "₺ ", parseInt(object.digit), ".", ",");
            }
            if (object.type === "stock"){
                return accounting.formatMoney(parseFloat(object.value).toFixed(parseInt(object.digit)), "", parseInt(object.digit), ".", ",");
            }
            if (object.type === "percent"){
                return parseInt(object.value).toString() + " %";
            }
            if (object.type === "commission"){
                return accounting.formatMoney(((((parseFloat(object.value) * parseInt(object.commissionRate)) / 100) + parseFloat(object.value)) + parseFloat(object.deposit)).toFixed(parseInt(object.digit)), "₺ ", parseInt(object.digit), ".", ",");
            }
            return "Send To [type] - [percent | stock | price]";
        },
        /**
         * @param {object} [object = {}]
         * @param {string} [object.type = price] - price | stock | percent - enter one of these
         * @param {string} [object.value = 0] - Like 0 or 12.55
         * @return string
         */
        saveData: function (object) {
            if (object.type === "price"){
                return parseFloat((((object.value).replace(/₺ /gi,"")).replace(/\./gi,"")).replace(/,/gi,".")).toString();
            }
            if (object.type === "stock"){
                return parseFloat((((object.value).replace(/ /gi,"")).replace(/\./gi,"")).replace(/,/gi,".")).toString();
            }
            if (object.type === "percent"){
                return parseInt((object.value).replace(/ %/gi,"")).toString();
            }
            return "Send To [type] - [percent | stock | price]";
        },
    },
    computed: {},
    watch: {}
});

$(document).ready(function (){
    $(".maskMoney").maskMoney({prefix:"₺ ", thousands:".", decimal:",", allowZero: true, precision: 2});
});