import nProgress from "nprogress";

export const onUploadProgress = (progressEvent) => {
  nProgress.set(progressEvent.progress);

  if (progressEvent.progress === 1) {
    nProgress.done();
  }
};
