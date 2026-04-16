(function () {
  /**
   * - Gather all `.js-budget-option` elements with a particular data-budget value
   * - Total up their values
   * - Update the `.js-budget-spent` element with the total
   */

  let selectorOption = ".js-budget-option";
  let selectorProgress = ".js-budget-progress";
  let dataBudget = "budget";
  let dataBudgetValue = "budgetValue";
  function update(budget) {
    let budgetTotal = 0;
    document.querySelectorAll(selectorOption + "[data-budget='" + budget + "']:checked, select" + selectorOption + "[data-budget='" + budget + "']").forEach((option) => {
      let value = 0;
      switch (option.nodeName) {
        case "INPUT":
          value = parseInt(option.dataset[dataBudgetValue]);
          break;
        case "SELECT":
          let selectedOption = option.options[option.selectedIndex];
          // console.log(selectedOption);
          value = parseInt(selectedOption.dataset[dataBudgetValue]);
          break;
        default:
          return;
      }

      if (!isNaN(value) && 0 < value) {
        console.log('Budget value: ' + value);
        budgetTotal += value;
      }
    });
    console.log("budgetTotal", budgetTotal);
    document.querySelector(selectorProgress + "[data-budget='" + budget + "']").value = budgetTotal;
  }
  document.addEventListener("change", (event) => {
    target = event.target;
    if (!target.dataset || !target.dataset[dataBudget]) {
      return;
    }
    let budget = target.dataset[dataBudget];
    update(budget);
  });
})();
