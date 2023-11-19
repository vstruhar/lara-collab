import useImageLoader from "@/hooks/useImageLoader";
import { Center, Image, Loader, Modal } from "@mantine/core";
import { useEffect } from "react";

export default function ImageModal({ image, opened, close }) {
  const { loadImage, loading } = useImageLoader();

  useEffect(() => {
    image && loadImage(image.path);
  }, [image]);

  return (
    <Modal
      title={image?.name}
      opened={opened}
      onClose={close}
      size="auto"
      centered
      overlayProps={{
        backgroundOpacity: 0.55,
        blur: 3,
      }}
    >
      {loading ? (
        <Center miw={150} maw="80vw" h={150}>
          <Loader color="blue" size="lg" />
        </Center>
      ) : (
        <Image
          src={image?.path}
          fit="contain"
          style={{
            maxHeight: "80vh",
          }}
        />
      )}
    </Modal>
  );
}
