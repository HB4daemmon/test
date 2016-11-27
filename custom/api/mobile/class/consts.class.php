<?php
require_once(dirname(__FILE__) . '/../../../util/mobile_global.php');

class MobileConsts{
    public static function getTerms(){
        return "Terms";
    }

    public static function getPolicy(){
        return <<<html
Our Privacy Policy was last updated and posted on October 24, 2015. It governs the privacy terms of our Website, located at www.cartgogogo.com. Any capitalized terms not defined in our Privacy Policy, have the meaning as specified in our Terms of Service.

Your Privacy
www.cartgogogo.com follows all legal requirements to protect your privacy. Our Privacy Policy is a legal statement that explains how we may collect information from you, how we may share your information, and how you can limit our sharing of your information. You will see terms in our Privacy Policy that are capitalized. These terms have meanings as described in the Definitions section below.

Definitions
"Non Personal Information" is information that is not personally identifiable to you and that we automatically collect when you access our Website with a web browser. It may also include publicly available information that is shared between you and others.

"Personally Identifiable Information" is non-public information that is personally identifiable to you and obtained in order for us to provide you within our Website. Personally Identifiable Information may include information such as your name, email address, and other related information that you provide to us or that we obtain about you.

Information We Collect
Generally, you control the amount and type of information you provide to us when using our Website.

As a Visitor, you can browse our website to find out more about our Website. You are not required to provide us with any Personally Identifiable Information as a Visitor.

Computer Information Collected
When you use our Website, we automatically collect certain computer information by the interaction of your mobile phone or web browser with our Website. Such information is typically considered Non Personal Information. We also collect the following:

    Cookies
    Our Website uses "Cookies" to identify the areas of our Website that you have visited. A Cookie is a small piece of data stored on your computer or mobile device by your web browser. We use Cookies to personalize the Content that you see on our Website. Most web browsers can be set to disable the use of Cookies. However, if you disable Cookies, you may not be able to access functionality on our Website correctly or at all. We never place Personally Identifiable Information in Cookies.

    Automatic Information
    We automatically receive information from your web browser or mobile device. This information includes the name of the website from which you entered our Website, if any, as well as the name of the website to which you're headed when you leave our website. This information also includes the IP address of your computer/proxy server that you use to access the Internet, your Internet Website provider name, web browser type, type of mobile device, and computer operating system. We use all of this information to analyze trends among our Users to help improve our Website.

How We Use Your Information
We use the information we receive from you as follows:

    Customizing Our Website
    We may use the Personally Identifiable information you provide to us along with any computer information we receive to customize our Website.

    Sharing Information with Affiliates and Other Third Parties
    We do not sell, rent, or otherwise provide your Personally Identifiable Information to third parties for marketing purposes. We may provide your Personally Identifiable Information to affiliates that provide services to us with regards to our Website (i.e. payment processors, Website hosting companies, etc.); such affiliates will only receive information necessary to provide the respective services and will be bound by confidentiality agreements limiting the use of such information.

    Data Aggregation
    We retain the right to collect and use any Non Personal Information collected from your use of our Website and aggregate such data for internal analytics that improve our Website and Service as well as for use or resale to others. At no time is your Personally Identifiable Information included in such data aggregations.

    Legally Required Releases of Information
    We may be legally required to disclose your Personally Identifiable Information, if such disclosure is (a) required by subpoena, law, or other legal process; (b) necessary to assist law enforcement officials or government enforcement agencies; (c) necessary to investigate violations of or otherwise enforce our Legal Terms; (d) necessary to protect us from legal action or claims from third parties including you and/or other Members; and/or (e) necessary to protect the legal rights, personal/real property, or personal safety of www.cartgogogo.com, our Users, employees, and affiliates.

Links to Other Websites
Our Website may contain links to other websites that are not under our direct control. These websites may have their own policies regarding privacy. We have no control of or responsibility for linked websites and provide these links solely for the convenience and information of our visitors. You access such linked Websites at your own risk. These websites are not subject to this Privacy Policy. You should check the privacy policies, if any, of those individual websites to see how the operators of those third-party websites will utilize your personal information. In addition, these websites may contain a link to Websites of our affiliates. The websites of our affiliates are not subject to this Privacy Policy, and you should check their individual privacy policies to see how the operators of such websites will utilize your personal information.

Privacy Policy Updates
We reserve the right to modify this Privacy Policy at any time. You should review this Privacy Policy frequently. If we make material changes to this policy, we may notify you on our Website, by a blog post, by email, or by any method we determine. The method we chose is at our sole discretion. We will also change the "Last Updated" date at the beginning of this Privacy Policy. Any changes we make to our Privacy Policy are effective as of this Last Updated date and replace any prior Privacy Policies.

Questions About Our Privacy Practices or This Privacy Policy
If you have any questions about our Privacy Practices or this Policy, please contact us.

Published with permission from www.cartgogogo.com.
html;
;
    }

    public static function getFreeDeliveryCount(){
        return 50;
    }

    public static function getHelpfulQuestions(){
        return <<<html
How do I use Cartgogogo?

                        -Welcome to Cartgogogo! First, please make an account so we can get to know you a little better (or, login with Facebook! ). Then, simply search or browse our website for the items you wish to order and select “Add to Cart”. When you are finished with your shopping list, go through the checkout and send the order to your personal shopper! Your order should be at your door at the time selected.

            Where does my order go after I submit?

                        -Once you submit your order, we match your order with one of our friendly shoppers. They’ll visit the store, pick up your order, and bring it to your door!

            Where does Cartgogogo deliver?

                        -Currently, we only serve in Champaign-Urbana, IL. However, we are excited to expand!

            What are your hours?

                        -We currently operate based on driver availability. If you would like to join the team, send us an email at cartgogogo@gmail.com!

            Can I use my credit/debit card?

                        -We accept all major credit cards  and debit cards. Shoppers can be tipped through your online order or in person with cash.

            How are goods on Cartgogogo priced?

                        -Cartgogogo operates by charging a small markup on the items purchased and a delivery fee. Orders over $50 are eligible for free delivery.

            How is my account information handled?

                        -When it comes to your personal information, we only ask for what we need in order to make your Cartgogogo experience awesome! We don’t share your personal information with any third party, and all payment information is processed by Stripe, so you know it’s secure.

             Can I reset/change my password?

                        -Absolutely! If you just need to change your password, visit “My Account” and go through the password change steps. If you need a password reset, go through the “Forgot Password ” steps and receive next steps via email.

             Can I change my delivery address?

                        -You can change your delivery address at any time through visiting “My Account”.

             Can I order a delivery for when I am not home?

                        -We need you to be your provided delivery address in order to receive your goods and ensure a high quality experience.

            How do I cancel an order?

                        -At this time, orders cannot be cancelled. We are hoping to add this feature soon.

            How do I reschedule an order?

                        -We currently do not allow reschedules.

            Can I place a personal note for my shopper?

                        -Absolutely! You can let your shopper know of personal preferences or other instructions in your shopping cart under the field marked “Notes ”.

            Can I tip my shopper?

                        -Tipping your shopper rewards the excellent work they do each and every day! Thank you!

            Can I add an item after placing my order?

                        -At this time, new items must be placed in a new order.

            How do I contact Cartgogogo?

                        -We’d love to talk with you! You can reach Cartgogogo anytime at cartgogogo@gmail.com. We are also available on Facebook!
html;

    }


}