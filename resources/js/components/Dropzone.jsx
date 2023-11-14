import { Box, Group, Image, SimpleGrid, Text, rem } from "@mantine/core";
import { Dropzone as MantineDropzone } from "@mantine/dropzone";
import { IconFile, IconFiles, IconUpload, IconX } from "@tabler/icons-react";

export default function Dropzone({ selected, onChange, ...props }) {
  const previews = selected.map((file, index) => {
    let icon = <IconFile style={{ width: rem(45), height: rem(45) }} />;

    if (file.type.includes("image")) {
      const imageUrl = URL.createObjectURL(file);

      icon = (
        <Image
          radius="md"
          maw={50}
          h="auto"
          src={imageUrl}
          onLoad={() => URL.revokeObjectURL(imageUrl)}
        />
      );
    }

    return (
      <Group key={index} wrap="nowrap">
        {icon}
        <Box maw="auto">
          <Text fz="md" fw={400} truncate="end">
            {file.name}
          </Text>
          <Text fz="xs" fw={300} c="dimmed">
            {file.type}
          </Text>
        </Box>
      </Group>
    );
  });

  return (
    <>
      <MantineDropzone
        onDrop={(files) => onChange(files)}
        onReject={(files) => console.log("rejected files", files)}
        {...props}
      >
        <Group
          justify="center"
          gap="md"
          mih={50}
          style={{ pointerEvents: "none" }}
        >
          <MantineDropzone.Accept>
            <IconUpload
              style={{
                width: rem(42),
                height: rem(42),
                color: "var(--mantine-color-blue-6)",
              }}
              stroke={1.5}
            />
          </MantineDropzone.Accept>
          <MantineDropzone.Reject>
            <IconX
              style={{
                width: rem(42),
                height: rem(42),
                color: "var(--mantine-color-red-6)",
              }}
              stroke={1.5}
            />
          </MantineDropzone.Reject>
          <MantineDropzone.Idle>
            <IconFiles
              style={{
                width: rem(42),
                height: rem(42),
                color: "var(--mantine-color-dimmed)",
              }}
              stroke={1.5}
            />
          </MantineDropzone.Idle>

          <div>
            <Text size="md" inline>
              Drag files here or click to select files
            </Text>
            <Text size="xs" c="dimmed" inline mt={7}>
              Files of any type will be accepted
            </Text>
          </div>
        </Group>
      </MantineDropzone>

      <SimpleGrid cols={2} m="lg">
        {previews}
      </SimpleGrid>
    </>
  );
}
