import { reloadWithQuery, reloadWithoutQueryParams } from "@/utils/route";
import { ActionIcon, Tooltip } from "@mantine/core";
import { useDisclosure } from "@mantine/hooks";
import { IconArchive } from "@tabler/icons-react";
import { useEffect } from "react";

export default function ArchivedFilterButton() {
  const [selected, { toggle }] = useDisclosure(
    route().params?.archived !== undefined,
  );

  useEffect(() => {
    if (selected) reloadWithQuery({ archived: 1 });
    else reloadWithoutQueryParams(["archived"]);
  }, [selected]);

  return (
    <Tooltip label="Archived" openDelay={500} withArrow>
      <ActionIcon
        variant={selected ? "filled" : "default"}
        color={selected ? "red" : ""}
        size="lg"
        onClick={toggle}
      >
        <IconArchive style={{ width: "60%", height: "60%" }} stroke={1.5} />
      </ActionIcon>
    </Tooltip>
  );
}
