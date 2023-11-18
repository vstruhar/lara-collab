import dayjs from "dayjs";

export const date = (date) => {
  return dayjs(date).format("D. MMM YYYY");
};

export const dateTime = (datetime) => {
  return dayjs(datetime).format("D. MMM YYYY H:mm") + 'h';
};
