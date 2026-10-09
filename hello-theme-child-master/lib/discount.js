<script>
const currentDate = new Date().toJSON().slice(0, 10);

function computeDiscount(box) {
  if (currentDate < '2023-10-17') {
    var f=document.getElementsByName("input_126.2");
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
      discount += 500;
    } else {
      discount -= 500;
    }
	
    if ( (discount >= 100000) && (discount <= 999999) ) {
      discountString = discount.toString();
      f[0].value = "-¥ " + discountString.substr(0, 3) + ',' + discountString.substr(3, 3);
    } 
	else if ( (discount >= 10000) && (discount <= 99999) ) {
      discountString = discount.toString();
      f[0].value = "-¥ " + discountString.substr(0, 2) + ',' + discountString.substr(2, 3);
    } else if ( (discount >= 1000) && (discount <= 9999) ) {
      discountString = discount.toString();
      f[0].value = "-¥ " + discountString.substr(0, 1) + ',' + discountString.substr(1, 3);
    } else f[0].value = "-¥ " + discount.toString();
  }
}

let wouldNo = document.getElementById('choice_48_3_1');
wouldNo.addEventListener('change', function() {
   var f=document.getElementsByName("input_119.2");
    f[0].value = "-¥ 0";
});

let wouldYes = document.getElementById('choice_48_3_0');
wouldYes.addEventListener('change', function() {
   var f=document.getElementsByName("input_119.2");
    f[0].value = "-¥ 0";
});


let AttPerDaysStructureMember1 = document.querySelector("input[name='input_18.1']");
let AttPerDaysStructureMember2 = document.querySelector("input[name='input_18.2']");
let AttPerDaysStructureMember3 = document.querySelector("input[name='input_18.3']");
let AttPerDaysStructureMember4 = document.querySelector("input[name='input_18.4']");
let AttPerDaysStructureMember5 = document.querySelector("input[name='input_18.5']");
let AttPerDaysStructureMember6 = document.querySelector("input[name='input_18.6']");
let AttPerDaysStructureMember7 = document.querySelector("input[name='input_18.7']");
let AttPerDaysStructureMember8 = document.querySelector("input[name='input_18.8']");

let AttPerDaysStructureSupporter1 = document.querySelector("input[name='input_20.1']");
let AttPerDaysStructureSupporter2 = document.querySelector("input[name='input_20.2']");
let AttPerDaysStructureSupporter3 = document.querySelector("input[name='input_20.3']");
let AttPerDaysStructureSupporter4 = document.querySelector("input[name='input_20.4']");
let AttPerDaysStructureSupporter5 = document.querySelector("input[name='input_20.5']");
let AttPerDaysStructureSupporter6 = document.querySelector("input[name='input_20.6']");
let AttPerDaysStructureSupporter7 = document.querySelector("input[name='input_20.7']");
let AttPerDaysStructureSupporter8 = document.querySelector("input[name='input_20.8']");

let AttPerDaysSimpleMember1 = document.querySelector("input[name='input_23.1']");
let AttPerDaysSimpleMember2 = document.querySelector("input[name='input_23.2']");
let AttPerDaysSimpleMember3 = document.querySelector("input[name='input_23.3']");
let AttPerDaysSimpleMember4 = document.querySelector("input[name='input_23.4']");
let AttPerDaysSimpleMember5 = document.querySelector("input[name='input_23.5']");
let AttPerDaysSimpleMember6 = document.querySelector("input[name='input_23.6']");
let AttPerDaysSimpleMember7 = document.querySelector("input[name='input_23.7']");
let AttPerDaysSimpleMember8 = document.querySelector("input[name='input_23.8']");

let AttPerDaysNewComer1 = document.querySelector("input[name='input_25.1']");
let AttPerDaysNewComer2 = document.querySelector("input[name='input_25.2']");
let AttPerDaysNewComer3 = document.querySelector("input[name='input_25.3']");
let AttPerDaysNewComer4 = document.querySelector("input[name='input_25.4']");
let AttPerDaysNewComer5 = document.querySelector("input[name='input_25.5']");
let AttPerDaysNewComer6 = document.querySelector("input[name='input_25.6']");
let AttPerDaysNewComer7 = document.querySelector("input[name='input_25.7']");
let AttPerDaysNewComer8 = document.querySelector("input[name='input_25.8']");

let AttPerDaysNonMember1 = document.querySelector("input[name='input_27.1']");
let AttPerDaysNonMember2 = document.querySelector("input[name='input_27.2']");
let AttPerDaysNonMember3 = document.querySelector("input[name='input_27.3']");
let AttPerDaysNonMember4 = document.querySelector("input[name='input_27.4']");
let AttPerDaysNonMember5 = document.querySelector("input[name='input_27.5']");
let AttPerDaysNonMember6 = document.querySelector("input[name='input_27.6']");
let AttPerDaysNonMember7 = document.querySelector("input[name='input_27.7']");
let AttPerDaysNonMember8 = document.querySelector("input[name='input_27.8']");

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
AttPerDaysStructureMember6.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysStructureMember7.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysStructureMember8.addEventListener('change', function() {
	computeDiscount(this.checked);
});

AttPerDaysStructureSupporter1.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysStructureSupporter2.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysStructureSupporter3.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysStructureSupporter4.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysStructureSupporter5.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysStructureSupporter6.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysStructureSupporter7.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysStructureSupporter8.addEventListener('change', function() {
	computeDiscount(this.checked);
});

AttPerDaysSimpleMember1.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysSimpleMember2.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysSimpleMember3.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysSimpleMember4.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysSimpleMember5.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysSimpleMember6.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysSimpleMember7.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysSimpleMember8.addEventListener('change', function() {
	computeDiscount(this.checked);
});

AttPerDaysNewComer1.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysNewComer2.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysNewComer3.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysNewComer4.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysNewComer5.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysNewComer6.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysNewComer7.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysNewComer8.addEventListener('change', function() {
	computeDiscount(this.checked);
});

AttPerDaysNonMember1.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysNonMember2.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysNonMember3.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysNonMember4.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysNonMember5.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysNonMember6.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysNonMember7.addEventListener('change', function() {
	computeDiscount(this.checked);
});
AttPerDaysNonMember8.addEventListener('change', function() {
	computeDiscount(this.checked);
});
</script>