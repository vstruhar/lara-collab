import dayjs from "dayjs";

export const date = (date) => {
  return dayjs(date).format("D. MMM YYYY");
};

export const time = (date) => {
  return dayjs(date).format("H:mm") + 'h';
};

export const day = (date) => {
  return dayjs(date).format("dddd");
};

export const dateTime = (datetime) => {
  return dayjs(datetime).format("D. MMM YYYY H:mm") + 'h';
};

export const diffForHumans = (datetime, withoutSuffix = false) => {
  return dayjs(datetime).fromNow(withoutSuffix);
};
