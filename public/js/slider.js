getPricelist = function(sum, length) {
    var pricelist = [];
    plists.forEach(function(plist){
        if (plist.credit_from <= sum && plist.credit_to > sum && plist.length_from <= length && plist.length_to > length) {
            pricelist = plist;
        }
    });
    return pricelist;
};

var sliderTime = document.getElementById("timeRange");
var outputTime = document.getElementById("timeValue");
var creditSum = document.getElementById("creditSum");
var sliderCredit = document.getElementById("creditRange");
var outputCredit = document.getElementById("creditValue");
var length = document.getElementById("length");
var credit = document.getElementById("credit");
var interest = document.getElementById("interest");
var admFee = document.getElementById("admFee");
var monthlyPayment = document.getElementById("monthlyPayment");
var mpn = document.getElementById("mpn");
var bvkkmn = document.getElementById("bvkkmn");

outputTime.innerHTML = sliderTime.value; // Display the default slider value
outputCredit.innerHTML = sliderCredit.value; // Display the default slider value
interest.innerHTML = getPricelist(sliderCredit.value, sliderTime.value).interest;
var interestVar = interest.innerHTML/100;
var sum = (interestVar * (parseInt(outputTime.innerText) / 12) * parseInt(outputCredit.innerText)) + parseInt(outputCredit.innerText);
creditSum.innerHTML = Math.round((sum + Number.EPSILON) * 100) / 100;
length.innerHTML = sliderTime.value;
credit.innerHTML = sliderCredit.value;
var payment = parseFloat(creditSum.innerHTML) / parseInt(length.innerHTML);
monthlyPayment.innerHTML = Math.round((payment + Number.EPSILON) * 100) / 100;
bvkkmn.innerHTML = interest.innerHTML; // dabar lygu, po to gali buti, kad prisides administraciniai, sutarciu mokesciai, visa kita.

// Update the current slider value (each time you drag the slider handle)
sliderTime.oninput = function() {
    interest.innerHTML = getPricelist(sliderCredit.value, sliderTime.value).interest;
    var interestVar = interest.innerHTML/100;
    outputTime.innerHTML = this.value;
    var sum = (interestVar * (parseInt(outputTime.innerText) / 12) * parseInt(outputCredit.innerText)) + parseInt(outputCredit.innerText);
    creditSum.innerHTML = Math.round((sum + Number.EPSILON) * 100) / 100;
    length.innerHTML = this.value;
    var payment = parseFloat(creditSum.innerHTML) / parseInt(length.innerHTML);
    monthlyPayment.innerHTML = Math.round((payment + Number.EPSILON) * 100) / 100;
    bvkkmn.innerHTML = interest.innerHTML;
}

// Update the current slider value (each time you drag the slider handle)
sliderCredit.oninput = function() {
    interest.innerHTML = getPricelist(sliderCredit.value, sliderTime.value).interest;
    var interestVar = interest.innerHTML / 100;
    outputCredit.innerHTML = this.value;
    var sum = (interestVar * (parseInt(outputTime.innerText) / 12) * parseInt(outputCredit.innerText)) + parseInt(outputCredit.innerText);
    creditSum.innerHTML = Math.round((sum + Number.EPSILON) * 100) / 100;
    credit.innerHTML = this.value;
    var payment = parseFloat(creditSum.innerHTML) / parseInt(length.innerHTML);
    monthlyPayment.innerHTML = Math.round((payment + Number.EPSILON) * 100) / 100;
    bvkkmn.innerHTML = interest.innerHTML;
}