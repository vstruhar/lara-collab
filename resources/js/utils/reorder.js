export const reorder = (list, startIndex, endIndex) => {
  const result = Array.from(list);
  const [removed] = result.splice(startIndex, 1);
  result.splice(endIndex, 0, removed);

  return result;
};

export const move = (tasks, sourceGroupId, destinationGroupId, source, destination) => {
  const sourceClone = Array.from(tasks[sourceGroupId]);
  const destClone = Array.from(tasks[destinationGroupId] || []);
  const [removed] = sourceClone.splice(source.index, 1);

  destClone.splice(destination.index, 0, removed);

  const result = {};

  result[sourceGroupId] = sourceClone;
  result[destinationGroupId] = destClone;

  return result;
};
