import { Flex, Text, Title } from "@mantine/core";

export function EmptyResult({ title, subtitle }) {
  return (
    <Flex
      gap="xs"
      justify="center"
      align="center"
      direction="column"
      wrap="nowrap"
      mih={"80vh"}
    >
      <Title order={2} ta="center">
        {title}
      </Title>
      <Text size="sm" ta="center" c="gray.6">
        {subtitle}
      </Text>
    </Flex>
  );
}
