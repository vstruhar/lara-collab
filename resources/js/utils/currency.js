export const money = (amount, currency = 'USD', minimumFractionDigits = 2) => {
  const formatter = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency,
    minimumFractionDigits,
  });

  return formatter.format(amount / 100);
};
