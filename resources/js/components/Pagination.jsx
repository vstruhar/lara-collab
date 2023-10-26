import { Pagination as MantinePagination, Group } from "@mantine/core";
import { router } from "@inertiajs/react";

export default function Pagination({ current, pages, routeName = null }) {
  if (!routeName) {
    routeName = route().current();
  }

  return (
    <MantinePagination.Root
      value={current}
      total={pages}
      siblings={2}
      onChange={(page) =>
        router.get(route(routeName, { ...route().params, page }))
      }
    >
      <Group gap={5} justify="center">
        <MantinePagination.Previous />
        <MantinePagination.Items />
        <MantinePagination.Next />
      </Group>
    </MantinePagination.Root>
  );
}
