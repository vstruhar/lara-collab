export const stopOnIgnoreLink = (event) => {
  if (
    event.target.dataset.ignoreLink ||
    event.target.parentNode.dataset.ignoreLink
  )
    event.preventDefault();
};
