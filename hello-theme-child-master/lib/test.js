<input name='input_3' type='radio' value='Yes'  id='choice_41_3_0'   />
<label for='choice_41_3_0' id='label_41_3_0' >Yes</label>
<input name='input_3' type='radio' value='No' checked='checked' id='choice_41_3_1'   />
<label for='choice_41_3_1' id='label_41_3_1' >No</label>


<label for='ginput_base_price_41_119'>Price:</label>
<input type='text' readonly name='input_119.2' id='ginput_base_price_41_119' value='&#165; 0' />
<input name='input_18.1' type='checkbox'  value='2023-12-09|4200'  id='choice_41_18_1' />
<label for='choice_41_18_1' id='label_41_18_1' >Saturday, December 9</label>
<input name='input_18.2' type='checkbox'  value='2023-12-10|4200'  id='choice_41_18_2' />
<label for='choice_41_18_2' id='label_41_18_2'>Sunday, December 10</label>
<input name='input_18.3' type='checkbox'  value='2023-12-11|4200'  id='choice_41_18_3' />
<label for='choice_41_18_3' id='label_41_18_3'>Monday, December 11</label>
<input name='input_18.4' type='checkbox'  value='2023-12-12|4200'  id='choice_41_18_4' />
<label for='choice_41_18_4' id='label_41_18_4'>Tuesday, December 12</label>
<input name='input_18.5' type='checkbox'  value='2023-12-13|4200'  id='choice_41_18_5' />
<label for='choice_41_18_5' id='label_41_18_5'>Wednesday, December 13<


const currentDate = new Date().toJSON().slice(0, 10);

function computeDiscount(box) {
  if (currentDate < '2023-10-17') {
    var f=document.getElementsByName("input_119.2");
    let val = f[0].value;
    l = val.length;
    pos = 0;
    for (var i=0; i < l-1; i++) {
      if (val[i] == ' ') {
        pos = i;
      }
    }
    let discount = parseInt(f[0].value.substr(pos,l-pos).replace(",", ""));
    if (box) {
      discount += 150000;
    } else {
      discount -= 150000;
    }
    
    if ( (discount >= 100000) && (discount <= 999999) ) { // 999,999
      discountString = discount.toString();
      f[0].value = "-¥ " + discountString.substr(0, 3) + ',' + discountString.substr(3, 3);
    } else if ( (discount >= 10000) && (discount <= 99999) ) { // 99,999
      discountString = discount.toString();
      f[0].value = "-¥ " + discountString.substr(0, 2) + ',' + discountString.substr(2, 3);
    } else if ( (discount >= 1000) && (discount <= 9999) ) { // 9,999
      discountString = discount.toString();
      f[0].value = "-¥ " + discountString.substr(0, 1) + ',' + discountString.substr(1, 3);
    } else f[0].value = "-¥ " + discount.toString(); // 999
  }
}

let AttPerDaysStructureMember1 = document.querySelector("input[name='input_18.1']");
let AttPerDaysStructureMember2 = document.querySelector("input[name='input_18.2']");
let AttPerDaysStructureMember3 = document.querySelector("input[name='input_18.3']");
let AttPerDaysStructureMember4 = document.querySelector("input[name='input_18.4']");
let AttPerDaysStructureMember5 = document.querySelector("input[name='input_18.5']");

let woulNo = document.getElementById('choice_41_3_1');
woulNo.addEventListener('change', function() {
   var f=document.getElementsByName("input_119.2");
    f[0].value = "-¥ 0";
});

let wouldYes = document.getElementById('choice_41_3_0');
wouldYes.addEventListener('change', function() {
   var f=document.getElementsByName("input_119.2");
    f[0].value = "-¥ 0";
});


AttPerDaysStructureMember1.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysStructureMember2.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysStructureMember3.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysStructureMember4.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysStructureMember5.addEventListener('change', function() {
	computeDiscount(this.checked);
});
