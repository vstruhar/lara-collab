
export const humanReadableTime = (minutes) => {
  let formattedMinutes = (minutes % 60).toString();

  if (formattedMinutes.length === 1) {
    formattedMinutes = `0${formattedMinutes}`;
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

export const isTimeValueValid = (value) => {
  // valid values: 1:00, 10:30, 28:59, 1,5, 2.5
  return /^(\d{1,2}:[0-5]{1}[0-9]{1})$|^(\d{1,3}\.\d{0,2})$|^(\d{1,3},\d{0,2})$|^(\d{1,3})$/.test(
    value,
  );
};
