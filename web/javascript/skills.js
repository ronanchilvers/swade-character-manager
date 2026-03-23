// Skills javascript

const SKILL_DIE_STEPS = [4, 6, 8, 10, 12];

function skillCost(selectedDie, attributeDie, baselineDie) {
  let points = 0;

  SKILL_DIE_STEPS.forEach((stepDie) => {
    if (stepDie <= baselineDie || stepDie > selectedDie) {
      return;
    }

    points += stepDie <= attributeDie ? 1 : 2;
  });

  return points;
}

function updateSkillSpend() {
  const summary = document.querySelector('.js-skills-summary');
  if (!summary) {
    return;
  }

  const total = parseInt(summary.dataset.total || '12', 10);
  let spent = 0;

  document.querySelectorAll('.js-skill-die').forEach((select) => {
    const selectedDie = parseInt(select.value || '0', 10);
    const attributeDie = parseInt(select.dataset.linkedAttributeDie || '4', 10);
    const baselineDie = parseInt(select.dataset.baselineDie || '0', 10);

    spent += skillCost(selectedDie, attributeDie, baselineDie);
  });

  const remaining = Math.max(0, total - spent);

  document.querySelectorAll('.js-skill-points-spent').forEach((el) => {
    el.textContent = spent;
  });

  document.querySelectorAll('.js-skill-points-total').forEach((el) => {
    el.textContent = total;
  });

  document.querySelectorAll('.js-skill-points-remaining').forEach((el) => {
    el.textContent = remaining;
  });
}

document.addEventListener('DOMContentLoaded', () => {
  updateSkillSpend();

  document.querySelectorAll('.js-skill-die').forEach((select) => {
    select.addEventListener('change', updateSkillSpend);
  });
});
