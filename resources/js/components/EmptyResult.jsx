import { Flex, Text, Title, useComputedColorScheme } from "@mantine/core";

export function EmptyResult({ title, subtitle }) {
  const computedColorScheme = useComputedColorScheme();

  return (
    <Flex
      gap="xs"
      justify="center"
      align="center"
      direction="column"
      wrap="nowrap"
      mih={"80vh"}
      opacity={0.5}
    >
      <Title order={2} ta="center" c={computedColorScheme === "light" ? "gray.8" : "gray"}>
        {title}
      </Title>
      <Text size="sm" ta="center" c={computedColorScheme === "light" ? "gray.6" : "gray.6"}>
        {subtitle}
      </Text>
    </Flex>
  );
}
