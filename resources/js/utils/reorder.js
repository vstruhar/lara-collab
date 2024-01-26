import cloneDeep from "lodash/cloneDeep";

export const reorder = (list, startIndex, endIndex) => {
  const result = Array.from(list);
  const [removed] = result.splice(startIndex, 1);
  result.splice(endIndex, 0, removed);

  return result;
};

export const move = (tasks, sourceGroupId, destinationGroupId, sourceIndex, destinationIndex) => {
  const sourceClone = cloneDeep(tasks[sourceGroupId]);
  const destClone = Array.from(tasks[destinationGroupId] || []);
  const [removed] = sourceClone.splice(sourceIndex, 1);

  removed.group_id = destinationGroupId;

  destClone.splice(destinationIndex, 0, removed);

  const result = {};

  result[sourceGroupId] = sourceClone;
  result[destinationGroupId] = destClone;

  return result;
};
