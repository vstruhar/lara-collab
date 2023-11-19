import { useState } from 'react';

export default function useImageLoader() {
  const [loadingState, setLoadingState] = useState({
    loading: true,
    error: null,
    image: null,
  });

  const loadImage = (url) => {
    const image = new Image();

    image.onload = () => {
      setLoadingState({
        loading: false,
        error: null,
        image,
      });
    };

    image.onerror = () => {
      setLoadingState({
        loading: false,
        error: 'Failed to load image',
        image: null,
      });
    };

    image.src = url;

    return () => {
      image.onload = null;
      image.onerror = null;
    };
  };

  return { ...loadingState, loadImage };
}
