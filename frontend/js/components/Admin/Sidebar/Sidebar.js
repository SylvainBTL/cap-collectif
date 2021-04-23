// @flow
import * as React from 'react';
import { useIntl } from 'react-intl';
import { useSelector } from 'react-redux';
import styled from 'styled-components';
import { type GlobalState, type FeatureToggles } from '~/types';
import AppBox from '~ui/Primitives/AppBox';
import Flex from '~ui/Primitives/Layout/Flex';
import Accordion from '~ds/Accordion';
import SidebarButton from './SidebarButton';
import SidebarLink from './SidebarLink';
import { ICON_NAME } from '~ds/Icon/Icon';
import { pxToRem } from '~/utils/styles/mixins';
import colors from '~/styles/modules/colors';
import Text from '~ui/Primitives/Text';
import Button from '~ds/Button/Button';
import useIsMobile from '~/utils/hooks/useIsMobile';
import { URL_MAP, CAP_COLLECTIF_SVG } from './Sidebar.utils';

export type Props = {| +appVersion: string |};

const SidebarAccordionItem = styled(Accordion.Item).attrs({ bg: 'gray.900', pb: 0 })``;
const SidebarAccordionPanel = styled(Accordion.Panel).attrs(({ isFirstRender, isOpen }) => ({
  bg: 'gray.800',
  pb: 2,
  transition: { duration: isFirstRender ? 0 : 0.2, ease: [0.04, 0.62, 0.23, 0.98] },
  display: `${isOpen ? 'flex' : 'none'} !important`,
}))``;

const cookieName = 'sidebar_is_opened';

export const Sidebar = ({ appVersion }: Props): React.Node => {
  const savedIsOpen = localStorage.getItem(cookieName);
  const isMobile = useIsMobile();
  const [isOpen, setIsOpen] = React.useState<boolean>(
    savedIsOpen ? savedIsOpen === 'true' : !isMobile,
  );
  const [isFirstRender, setIsFirstRender] = React.useState<boolean>(true);
  const intl = useIntl();
  const features: FeatureToggles = useSelector((state: GlobalState) => state.default.features);
  let defaultAccordion = '';
  const keys = Object.keys(URL_MAP);
  for (const key of keys) {
    if (URL_MAP[key].some(val => window.location.href.includes(val))) defaultAccordion = key;
  }
  React.useEffect(() => {
    const sonataNavbar = document.querySelector('nav.navbar.navbar-static-top');
    const sonataContent = document.querySelector('.content-admin');
    if (sonataNavbar && sonataContent && !isMobile) {
      sonataNavbar.style.marginLeft = !isOpen ? '56px' : '230px';
      sonataContent.style.width = !isOpen ? 'calc(100vw - 56px)' : 'calc(100vw - 224px)';
    }
    setIsFirstRender(false);
    // we only want this on our first render
    // eslint-disable-next-line
  }, []);
  return (
    <AppBox
      as="aside"
      bg="gray.900"
      width={pxToRem(isOpen ? 224 : 56)}
      // Don't ask me why, Sonata
      css={{ transition: 'width 0.3s ease-in-out', zIndex: 1031 }}
      height={isMobile && !isOpen ? '56px' : '100%'}
      position={isMobile ? 'absolute' : 'unset'}>
      <AppBox
        position={isMobile ? 'absolute' : 'fixed'}
        top={0}
        left={0}
        bg={isMobile && !isOpen ? 'white' : 'gray.900'}
        overflow="hidden"
        height="100%"
        width={pxToRem(isOpen ? 224 : 56)}
        onMouseEnter={() => {
          const sonataNavbar = document.querySelector('nav.navbar.navbar-static-top');
          const sonataContent = document.querySelector('.content-admin');
          if (!isOpen) {
            if (sonataNavbar && !isMobile) sonataNavbar.style.marginLeft = '230px';
            if (sonataContent && !isMobile) sonataContent.style.width = 'calc(100vw - 224px)';
            localStorage.setItem(cookieName, 'true');
            setIsOpen(true);
          }
        }}
        css={{ transition: 'width 0.3s ease-in-out', zIndex: 1031 }}>
        <Flex
          boxShadow="0px 4px 13px rgb(0 0 0 / 20%);"
          p={4}
          justifyContent="space-between"
          position="relative">
          <a href="/" style={{ overflow: 'hidden' }}>
            {CAP_COLLECTIF_SVG}
          </a>
          <Button
            onClick={() => {
              const sonataNavbar = document.querySelector('nav.navbar.navbar-static-top');
              const sonataContent = document.querySelector('.content-admin');
              if (sonataNavbar && sonataContent && !isMobile) {
                sonataNavbar.style.marginLeft = isOpen ? '56px' : '230px';
                sonataContent.style.width = isOpen ? 'calc(100vw - 56px)' : 'calc(100vw - 224px)';
              }
              localStorage.setItem(cookieName, isOpen ? 'false' : 'true');
              setIsOpen(!isOpen);
            }}>
            <svg
              width="24px"
              height="24px"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path
                d="M6 8.33333H19"
                stroke={isMobile && !isOpen ? colors.blue[900] : '#FAFCFF'}
                strokeLinecap="round"
                strokeLinejoin="round"
              />
              <path
                d="M6 12.3334H19"
                stroke={isMobile && !isOpen ? colors.blue[900] : '#FAFCFF'}
                strokeLinecap="round"
                strokeLinejoin="round"
              />
              <path
                d="M6 16.3334H19"
                stroke={isMobile && !isOpen ? colors.blue[900] : '#FAFCFF'}
                strokeLinecap="round"
                strokeLinejoin="round"
              />
            </svg>
          </Button>
        </Flex>
        <Accordion
          spacing={0}
          height="calc(100% - 105px)"
          defaultAccordion={defaultAccordion}
          css={{
            overflowY: 'scroll',
            msOverflowStyle: 'none',
            scrollbarWidth: 'none',
          }}>
          <SidebarAccordionItem id="contributions">
            <SidebarButton icon={ICON_NAME.PENCIL_O} text="global.contribution" isOpen={isOpen} />
            <SidebarAccordionPanel isOpen={isOpen} isFirstRender={isFirstRender}>
              {features.reporting && (
                <SidebarLink text="admin.label.reporting" url="/admin/reporting" />
              )}
              <SidebarLink text="admin.label.proposal" url="/admin/capco/app/proposal/list" />
              <SidebarLink text="admin.label.opinion" url="/admin/capco/app/opinion/list" />
              <SidebarLink text="admin.label.reply" url="/admin/capco/app/reply/list" />
              <SidebarLink
                text="admin.label.opinion_version"
                url="/admin/capco/app/opinionversion/list"
              />
              <SidebarLink text="admin.label.argument" url="/admin/capco/app/argument/list" />
              <SidebarLink text="admin.label.source" url="/admin/capco/app/source/list" />
              <SidebarLink text="admin.label.comment" url="/admin/capco/app/comment/list" />
            </SidebarAccordionPanel>
          </SidebarAccordionItem>
          <SidebarAccordionItem id="contenus">
            <SidebarButton icon={ICON_NAME.FOLDER_O} text="admin.group.content" isOpen={isOpen} />
            <SidebarAccordionPanel isOpen={isOpen} isFirstRender={isFirstRender}>
              <SidebarLink
                text="admin.label.highlighted"
                url="/admin/capco/app/highlightedcontent/list"
              />
              <SidebarLink text="admin.label.theme" url="/admin/capco/app/theme/list" />
              <SidebarLink text="admin.label.post" url="/admin/capco/app/post/list" />
              {features.calendar && (
                <SidebarLink text="admin.label.events" url="/admin/capco/app/event/list" />
              )}
              <SidebarLink text="admin.label.video" url="/admin/capco/app/video/list" />
              <SidebarLink text="admin.label.page" url="/admin/capco/app/page/list" />
              <SidebarLink text="media" url="/admin/capco/media/media/list" />
            </SidebarAccordionPanel>
          </SidebarAccordionItem>
          <SidebarAccordionItem id="projets">
            <SidebarButton
              icon={ICON_NAME.BOOK_STAR_O}
              text="admin.group.project"
              isOpen={isOpen}
            />
            <SidebarAccordionPanel isOpen={isOpen} isFirstRender={isFirstRender}>
              <SidebarLink text="admin.label.project" url="/admin/capco/app/project/list" />
              <SidebarLink text="admin.label.appendix" url="/admin/capco/app/appendixtype/list" />
              <SidebarLink text="admin.label.category" url="/admin/capco/app/sourcecategory/list" />
              <SidebarLink
                text="admin.label.consultation"
                url="/admin/capco/app/consultation/list"
              />
              <SidebarLink
                text="admin.label.proposal_form"
                url="/admin/capco/app/proposalform/list"
              />
              <SidebarLink
                text="admin.label.questionnaire"
                url="/admin/capco/app/questionnaire/list"
              />
              <SidebarLink text="admin.label.pages.types" url="/admin/capco/app/projecttype/list" />
            </SidebarAccordionPanel>
          </SidebarAccordionItem>
          <SidebarAccordionItem id="utilisateurs">
            <SidebarButton icon={ICON_NAME.USER_O} text="sonata.admin.group.user" isOpen={isOpen} />
            <SidebarAccordionPanel isOpen={isOpen} isFirstRender={isFirstRender}>
              <SidebarLink text="global.select_user.type" url="/admin/capco/user/user/list" />
              <SidebarLink
                text="admin-menu-invite-users-label"
                url="/admin/capco/user/invite/list"
              />
              <SidebarLink text="admin.label.group" url="/admin/capco/app/group/list" />
              <SidebarLink text="admin.label.user_type" url="/admin/capco/user/usertype/list" />
              <SidebarLink
                text="admin.label.newsletter_subscription"
                url="/admin/capco/app/newslettersubscription/list"
              />
            </SidebarAccordionPanel>
          </SidebarAccordionItem>
          <SidebarAccordionItem id="reglages">
            <SidebarButton icon={ICON_NAME.COG_O} text="admin.group.parameters" isOpen={isOpen} />
            <SidebarAccordionPanel isOpen={isOpen} isFirstRender={isFirstRender}>
              <SidebarLink text="admin.label.menu_item" url="/admin/capco/app/menuitem/list" />
              <SidebarLink
                text="admin.label.social_network"
                url="/admin/capco/app/socialnetwork/list"
              />
              <SidebarLink
                text="admin.label.footer_social_network"
                url="/admin/capco/app/footersocialnetwork/list"
              />
              <SidebarLink
                text="admin.label.project_district"
                url="/admin/capco/app/district-projectdistrict/list"
              />
              <SidebarLink text="admin.fields.proposal_form.map" url="/admin/map/list" />
              {features.multilangue && (
                <SidebarLink text="global-languages" url="/admin/locale/list" />
              )}
              <SidebarLink text="custom-url" url="/admin/redirect/list" />
              <SidebarLink text="website-icon" url="/admin/favicon/list" />
              <SidebarLink text="global-typeface" url="/admin/font/list" />
              <SidebarLink
                text="admin.label.settings.global"
                url="/admin/settings/settings.global/list"
              />
              <SidebarLink
                text="admin.label.settings.performance"
                url="/admin/settings/settings.performance/list"
              />
              <SidebarLink
                text="admin.label.settings.modules"
                url="/admin/settings/settings.modules/list"
              />
              <SidebarLink
                text="admin.label.settings.notifications"
                url="/admin/settings/settings.notifications/list"
              />
              <SidebarLink
                text="admin.label.settings.appearance"
                url="/admin/settings/settings.appearance/list"
              />
            </SidebarAccordionPanel>
          </SidebarAccordionItem>
          <SidebarAccordionItem id="pages">
            <SidebarButton icon={ICON_NAME.FILE_O} text="admin.group.pages" isOpen={isOpen} />
            <SidebarAccordionPanel isOpen={isOpen} isFirstRender={isFirstRender}>
              <SidebarLink text="admin.label.section" url="/admin/capco/app/section/list" />
              <SidebarLink text="admin.label.pages.contact" url="/admin/contact/list" />
              <SidebarLink
                text="admin.label.pages.homepage"
                url="/admin/settings/pages.homepage/list"
              />
              <SidebarLink text="admin.label.pages.blog" url="/admin/settings/pages.blog/list" />
              <SidebarLink
                text="admin.label.pages.events"
                url="/admin/settings/pages.events/list"
              />
              <SidebarLink
                text="admin.label.pages.themes"
                url="/admin/settings/pages.themes/list"
              />
              <SidebarLink
                text="admin.label.pages.projects"
                url="/admin/settings/pages.projects/list"
              />
              <SidebarLink
                text="admin.label.pages.registration"
                url="/admin/settings/pages.registration/list"
              />
              {features.members_list && (
                <SidebarLink
                  text="admin.label.pages.members"
                  url="/admin/settings/pages.members/list"
                />
              )}
              <SidebarLink text="admin.label.pages.login" url="/admin/settings/pages.login/list" />
              <SidebarLink
                text="admin.label.pages.footer"
                url="/admin/settings/pages.footer/list"
              />
              <SidebarLink
                text="admin.label.pages.cookies"
                url="/admin/settings/pages.cookies/list"
              />
              <SidebarLink
                text="admin.label.pages.privacy"
                url="/admin/settings/pages.privacy/list"
              />
              <SidebarLink text="admin.label.pages.legal" url="/admin/settings/pages.legal/list" />
              <SidebarLink
                text="admin.label.pages.charter"
                url="/admin/settings/pages.charter/list"
              />
              <SidebarLink
                text="admin.label.pages.shield"
                url="/admin/settings/pages.shield/list"
              />
            </SidebarAccordionPanel>
          </SidebarAccordionItem>
          {features.unstable__emailing ? (
            <SidebarAccordionItem id="emailing">
              <SidebarButton
                icon={ICON_NAME.ENVELOPE_O}
                text="admin.group.emailing"
                isOpen={isOpen}
              />
              <SidebarAccordionPanel isOpen={isOpen} isFirstRender={isFirstRender}>
                <SidebarLink text="admin-menu-campaign-list" url="/admin/mailingCampaign/list" />
                <SidebarLink text="admin-menu-emailing-list" url="/admin/mailingList/list" />
              </SidebarAccordionPanel>
            </SidebarAccordionItem>
          ) : null}
        </Accordion>
        {isOpen && (
          <Text
            as="span"
            color="gray.700"
            position="absolute"
            bottom={5}
            fontSize={11}
            textAlign="center"
            width="100%">
            {`${intl.formatMessage({ id: 'app-version' })} ${appVersion}`}
          </Text>
        )}
      </AppBox>
    </AppBox>
  );
};

export default Sidebar;
