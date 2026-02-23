import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export const usePermissions = () => {
    const page = usePage()

    const userPermissions = computed<string[]>(() => {
        const props = page.props as Record<string, unknown>
        return (props.permissions as string[]) ?? []
    });

    const can = (permission: string): boolean => {
        return userPermissions.value.includes(permission)
    };

    const canAny = (permissions: string[]): boolean => {
        return permissions.some((p) => userPermissions.value.includes(p))
    };

    const canAll = (permissions: string[]): boolean => {
        return permissions.every((p) => userPermissions.value.includes(p))
    };

    return { can, canAny, canAll, userPermissions };
}
