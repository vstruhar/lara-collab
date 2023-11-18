
export const humanReadableTime = (minutes) => {
  let formattedMinutes = (minutes % 60).toString();

  if (formattedMinutes.length === 1) {
    formattedMinutes += '0';
  }
  return `${Math.floor(minutes / 60)}:${formattedMinutes}`;
};

export const convertToMinutes = (value) => {
  if (value.includes(':')) { // 10:30
    return (value.split(':')[0] * 60) + parseInt(value.split(':')[1]);
  } else if (value.includes('.')) { // 1.5 or 1.75
    let remainder = value.split('.')[1];

    if (remainder.length === 1) {
      remainder += '0';
    }
    return (value.split('.')[0] * 60) + (remainder * 0.6);
  } else if (value.includes(',')) { // 1,5 or 1,75
    let remainder = value.split(',')[1];

    if (remainder.length === 1) {
      remainder += '0';
    }
    return (value.split(',')[0] * 60) + (remainder * 0.6);
  }
  return value * 60;
}
